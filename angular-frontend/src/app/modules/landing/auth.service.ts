import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

interface LoginData {
  email: string;
  password: string;
}

interface RegisterData {
  username: string;
  email: string;
  password: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private registerUrl = 'http://localhost:8880/api/auth/register';
  private loginUrl = 'http://localhost:8880/api/auth/login';

  constructor(private http: HttpClient) {}

  login(userData: LoginData): Observable<any> {
    return this.http.post(this.loginUrl, userData);
  }

  register(userData: RegisterData): Observable<any> {
    return this.http.post(this.registerUrl, userData);
  }
}
