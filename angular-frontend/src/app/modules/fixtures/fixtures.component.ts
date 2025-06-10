import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FixturesService } from './fixtures.service';
import { ForecastService } from './forecast.service'; 
import { AuthService } from '../landing/auth.service';
import { ToastrService } from 'ngx-toastr';

// Interfaces para tipado
interface Team {
  name: string;
  
}

interface Fixture {
  id: number;
  date: string;
}

interface Game {
  fixture: Fixture;
  teams: {
    home: Team;
    away: Team;
  };
  league: {
    name: string;
  };
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
  userVotes: Set<number> = new Set(); // Para saber en qué partidos ya votó el usuario

  sendingForecast: boolean = false;
  errorMessage: string = '';

  constructor(
    private fixturesService: FixturesService,
    private forecastService: ForecastService,
    private authService: AuthService,
    private toastr: ToastrService
  ) { }

  ngOnInit(): void {
    const leagues = ['39', '140', '135', '78', '61']; // Las 5 grandes ligas
    const season = '2023';

    leagues.forEach(leagueId => {
      this.fixturesService.getFixtures(leagueId, season).subscribe({
        next: (res) => {
          if (res && res.response && res.response.length > 0) {
            const leagueName = res.response[0]?.league?.name || 'Desconocida';
            if (!this.fixturesByLeague[leagueName]) {
              this.fixturesByLeague[leagueName] = [];
            }
            this.fixturesByLeague[leagueName].push(...res.response);

            // Cargar votos para cada partido
            res.response.forEach((game: Game) => {
              if (game?.fixture?.id) {
                this.loadVotes(game.fixture.id);
              }
            });
          } else {
            console.warn(`No se encontraron datos para la liga ${leagueId}`);
          }
        },
        error: (err) => {
          console.error('Error fetching fixtures:', err);
          this.toastr.error('Error al cargar los partidos: ' + (err.error?.message || 'Error desconocido'));
        }
      });
    });

    // Cargar votos previos del usuario
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

  /**
   * Método para enviar pronóstico (1X2)
   * @param game Objeto del partido
   * @param result '1', 'X' o '2'
   */
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
      localTeamName: game.teams.home.name, // Ajustado al nombre esperado por el backend
      awayTeamName: game.teams.away.name,  // Ajustado al nombre esperado por el backend
      date: game.fixture.date,
      competition: game.league.name
    };

    this.forecastService.sendForecast(gameData, result).subscribe({
      next: () => {
        this.sendingForecast = false;
        this.toastr.success('Pronóstico enviado correctamente');
        this.userVotes.add(game.fixture.id); // Marcar como votado
        this.loadVotes(game.fixture.id); // Recargar votos para actualizar porcentajes
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

  /**
   * Carga los votos y porcentajes para un partido
   * @param gameId ID del partido
   */
  loadVotes(gameId: number): void {
    this.forecastService.getVotes(gameId).subscribe({
      next: (data: VoteData) => {
        this.votesByGame[gameId] = data;
      },
      error: (err) => {
        console.error('Error cargando votos:', err);
        this.toastr.error('Error al cargar los votos: ' + (err.error?.message || 'Error desconocido'));
      }
    });
  }

  /**
   * Carga los partidos en los que el usuario ya votó
   */
  loadUserVotes(): void {
    const currentUser = this.authService.getCurrentUser();
    if (!currentUser) {
      return;
    }
    // Asume que tienes un endpoint en ForecastService para obtener los votos del usuario
    this.forecastService.getUserVotes().subscribe({
      next: (votes: number[]) => {
        this.userVotes = new Set(votes); // Asume que el backend devuelve un array de game IDs
      },
      error: (err) => {
        console.error('Error cargando votos del usuario:', err);
        this.toastr.error('Error al cargar tus votos previos');
      }
    });
  }

  /**
   * Verifica si el usuario ya votó en un partido
   * @param gameId ID del partido
   */
  hasVoted(gameId: number): boolean {
    return this.userVotes.has(gameId);
  }
}