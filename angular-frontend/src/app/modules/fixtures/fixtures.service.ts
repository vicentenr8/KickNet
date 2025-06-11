import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class FixturesService {

  private apiUrl = 'https://v3.football.api-sports.io/fixtures';
  private headers = new HttpHeaders({
    'x-rapidapi-host': 'v3.football.api-sports.io',
    'x-rapidapi-key': '6f1093b225adc776e36107c1414094ee'
  });

  constructor(private http: HttpClient) { }

  getFixtures(leagueId: string, season: string): Observable<any> {
    const params = new HttpParams()
      .set('league', leagueId)
      .set('season', season);

    return this.http.get(this.apiUrl, { headers: this.headers, params });
  }

  getFixtureById(fixtureId: number): Observable<any> {
    const url = `${this.apiUrl}/${fixtureId}`;
    return this.http.get(url, { headers: this.headers });
  }

}



