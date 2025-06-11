import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FixturesService } from './fixtures.service';
import { ForecastService } from './forecast.service';
import { AuthService } from '../landing/auth.service';
import { ToastrService } from 'ngx-toastr';
import { forkJoin } from 'rxjs';

interface Team {
  name: string;
}

interface Fixture {
  id: number;
  date: string;
}

interface Game {
  fixture: Fixture;
  teams: { home: Team; away: Team };
  league: { name: string };
}

interface VoteData {
  votes: { [key: string]: number };
  percentages: { [key: string]: number };
  totalVotes: number;
}

@Component({
  selector: 'app-fixtures',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './fixtures.component.html',
})
export class FixturesComponent implements OnInit {
  fixturesByLeague: { [leagueName: string]: Game[] } = {};
  openLeagues: Set<string> = new Set();
  votesByGame: { [gameId: number]: VoteData } = {};
  userVotes: Set<number> = new Set();
  sendingForecast: boolean = false;
  errorMessage: string = '';
  isLoading: boolean = true;

  constructor(
    private fixturesService: FixturesService,
    private forecastService: ForecastService,
    private authService: AuthService,
    private toastr: ToastrService
  ) {}

  ngOnInit(): void {
    const leagues = ['39', '140', '135', '78', '61'];
    const season = '2023';

    const fixtureRequests = leagues.map(leagueId =>
      this.fixturesService.getFixtures(leagueId, season)
    );

    forkJoin(fixtureRequests).subscribe({
      next: (responses) => {
        responses.forEach((res, index) => {
          const leagueId = leagues[index];
          if (res && res.response && res.response.length > 0) {
            const leagueName = res.response[0]?.league?.name || 'Desconocida';
            this.fixturesByLeague[leagueName] = res.response; // Asignar directamente
            console.log(`Partidos cargados para liga ${leagueName}:`, res.response.length, 'partidos');
            res.response.forEach((game: Game) => {
              console.log(`Partido ID: ${game.fixture.id}, Equipos: ${game.teams.home.name} vs ${game.teams.away.name}`);
            });
          } else {
            console.warn(`No se encontraron datos para la liga ${leagueId}`);
          }
        });
        this.isLoading = false;
      },
      error: (err) => {
        console.error('Error al obtener partidos:', err);
        this.toastr.error('Error al cargar los partidos: ' + (err.error?.message || 'Error desconocido'));
        this.isLoading = false;
      }
    });

    this.loadUserVotes();
  }

  getLeagues(): string[] {
    return Object.keys(this.fixturesByLeague);
  }

  LeagueOpener(leagueName: string): void {
    if (this.openLeagues.has(leagueName)) {
      this.openLeagues.delete(leagueName);
    } else {
      this.openLeagues.add(leagueName);
    }
  }

  isLeagueOpen(leagueName: string): boolean {
    return this.openLeagues.has(leagueName);
  }

  sendForecast(game: Game, result: string): void {
    const currentUser = this.authService.getCurrentUser();
    if (!currentUser) {
      this.sendingForecast = false;
      this.errorMessage = 'Usuario no autenticado, por favor inicia sesión';
      this.toastr.error(this.errorMessage);
      return;
    }

    if (this.userVotes.has(game.fixture.id)) {
      this.toastr.info('Ya has votado en este partido');
      return;
    }

    this.sendingForecast = true;
    this.errorMessage = '';

    const gameData = {
      game_id: game.fixture.id,
      localTeamName: game.teams.home.name,
      awayTeamName: game.teams.away.name,
      date: game.fixture.date,
      competition: game.league.name
    };

    this.forecastService.sendForecast(gameData, result).subscribe({
      next: () => {
        this.sendingForecast = false;
        this.toastr.success('Pronóstico enviado correctamente');
        this.userVotes.add(game.fixture.id);
        this.loadVotes(game.fixture.id);
      },
      error: (err) => {
        this.sendingForecast = false;
        const errorMsg = err.error?.message || err.message || 'Error desconocido';
        this.errorMessage = errorMsg;
        this.toastr.error('Error al enviar el pronóstico: ' + errorMsg);
        console.error(err);
      }
    });
  }

  loadVotes(gameId: number): void {
    console.log(`Cargando votos para el partido ID: ${gameId}`);
    this.forecastService.getVotes(gameId).subscribe({
      next: (data: VoteData) => {
        console.log(`Votos recibidos para ${gameId}:`, data);
        this.votesByGame[gameId] = data;
      },
      error: (err) => {
        if (err.status === 404) {
          console.warn(`No se encontraron votos para el partido ID ${gameId}`);
          this.votesByGame[gameId] = { votes: { '1': 0, 'X': 0, '2': 0 }, percentages: { '1': 0, 'X': 0, '2': 0 }, totalVotes: 0 };
        } else {
          console.error('Error cargando votos:', err);
          this.toastr.error('Error al cargar los votos: ' + (err.error?.message || 'Error desconocido'));
        }
      }
    });
  }

  loadUserVotes(): void {
    const currentUser = this.authService.getCurrentUser();
    if (!currentUser) {
      return;
    }
    this.forecastService.getUserVotes().subscribe({
      next: (votes: number[]) => {
        this.userVotes = new Set(votes);
      },
      error: (err) => {
        console.error('Error cargando votos del usuario:', err);
        this.toastr.error('Error al cargar tus votos previos');
      }
    });
  }

  hasVoted(gameId: number): boolean {
    return this.userVotes.has(gameId);
  }
}