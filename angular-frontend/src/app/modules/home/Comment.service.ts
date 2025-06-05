import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root',
})
export class CommentService {
  private apiUrl = 'http://localhost:8880/api/comments';

  constructor(private http: HttpClient) {}

  // Obtener todos los comentarios
  getComments() {
    return this.http.get(this.apiUrl);
  }

  // Crear comentario
  createComment(data: { content: string; user_id: number; publication_id: number }) {
    return this.http.post(this.apiUrl, data);
  }
  
}
