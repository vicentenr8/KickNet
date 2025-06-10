import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ForecastService {
  private apiUrl = 'http://localhost:8880/api/forecast';

  constructor(private http: HttpClient) {}

  sendForecast(gameData: { 
    game_id: number; 
    localTeamName: string; 
    awayTeamName: string; 
    date: string; 
    competition: string;
  }, result: string): Observable<any> {
    const payload = {
      game_id: gameData.game_id,
      localTeamName: gameData.localTeamName,
      awayTeamName: gameData.awayTeamName,
      date: gameData.date,
      competition: gameData.competition,
      result: result
    };
    return this.http.post(this.apiUrl, payload);
  }

  getVotes(gameId: number): Observable<{ votes: { [key: string]: number }, percentages: { [key: string]: number }, totalVotes: number }> {
    return this.http.get<{ votes: { [key: string]: number }, percentages: { [key: string]: number }, totalVotes: number }>(`${this.apiUrl}/votes/${gameId}`);
  }

  getUserVotes(): Observable<number[]> {
    return this.http.get<number[]>(`${this.apiUrl}/user-votes`);
  }
}