import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FixturesService } from './fixtures.service';
import { ForecastService } from './forecast.service'; 
import { AuthService } from '../landing/auth.service';

@Component({
  selector: 'app-fixtures',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './fixtures.component.html',
  // styleUrls: ['./fixtures.component.css'] // si tienes estilos
})
export class FixturesComponent implements OnInit {

  fixturesByLeague: { [leagueName: string]: any[] } = {};
  openLeagues: Set<string> = new Set();

  // Opcional: para saber si se está enviando un pronóstico (puedes controlar loading y errores)
  sendingForecast: boolean = false;
  errorMessage: string = '';

  constructor(
    private fixturesService: FixturesService,
    private forecastService: ForecastService,
    private authService: AuthService // para enviar pronósticos
  ) { }

  ngOnInit(): void {
    const leagues = ['39', '140', '135', '78', '61']; // Las 5 grandes ligas
    const season = '2023';

    leagues.forEach(leagueId => {
      this.fixturesService.getFixtures(leagueId, season).subscribe({
        next: (res) => {
          const leagueName = res.response[0]?.league.name || 'Desconocida';
          if (!this.fixturesByLeague[leagueName]) {
            this.fixturesByLeague[leagueName] = [];
          }
          this.fixturesByLeague[leagueName].push(...res.response);
        },
        error: (err) => {
          console.error('Error fetching fixtures:', err);
        }
      });
    });
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
   * @param gameId id del partido
   * @param result '1', 'X' o '2'
   */
  sendForecast(gameId: number, result: string): void {
    this.sendingForecast = true;
    this.errorMessage = '';
  
    const currentUser = this.authService.getCurrentUser();
    const userId = currentUser ? currentUser.id : null;
  
    if (!userId) {
      this.sendingForecast = false;
      this.errorMessage = 'Usuario no autenticado, por favor inicia sesión';
      return;
    }
  
    this.forecastService.sendForecast(gameId, result).subscribe({
      next: () => {
        this.sendingForecast = false;
        alert('Pronóstico enviado con éxito');
      },
      error: (err) => {
        this.sendingForecast = false;
        this.errorMessage = 'Error enviando pronóstico';
        console.error(err);
      }
    });
  }
}