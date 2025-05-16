import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FixturesService } from './fixtures.service';

@Component({
  selector: 'app-fixtures',
  imports: [CommonModule],
  templateUrl: './fixtures.component.html',
  //styleUrl: './fixtures.component.css'
})
export class FixturesComponent implements OnInit {
  fixturesByLeague: { [leagueName: string]: any[] } = {};
  openLeagues: Set<string> = new Set();
  getLeagues(): string[] {
    return Object.keys(this.fixturesByLeague);
  }

  constructor(private fixturesService: FixturesService) { }

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

 LeagueOpener(leagueName: string) {
  if (this.openLeagues.has(leagueName)) {
    this.openLeagues.delete(leagueName);
  } else {
    this.openLeagues.add(leagueName);
  }
}

isLeagueOpen(leagueName: string): boolean {
  return this.openLeagues.has(leagueName);
}
}