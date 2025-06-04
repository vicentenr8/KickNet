import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class PublicationService {
  private apiUrl = 'http://localhost:8000/api/publications'; // cambia el puerto si es necesario

  constructor(private http: HttpClient) {}

  createPublication(data: any) {
    return this.http.post(this.apiUrl, data);
  }

  getPublications() {
    return this.http.get(this.apiUrl);
  }
}
