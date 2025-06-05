
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ForecastService {

  private apiUrl = 'http://localhost:8880/api/forecast';

  constructor(private http: HttpClient) {}

  sendForecast( gameId: number, result: string): Observable<any> {
    const payload = {
      game_id: gameId,
      result: result
    };
    return this.http.post(this.apiUrl, payload);
  }
}
