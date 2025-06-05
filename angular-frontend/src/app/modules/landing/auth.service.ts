// src/app/auth.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable} from 'rxjs';
import { map, tap } from 'rxjs/operators';
import { Router } from '@angular/router';

interface LoginData {
  email: string;
  password: string;
}

interface RegisterData {
  username: string;
  email: string;
  password: string;
}

export interface User {
  id: number;
  email: string;
  username: string;
  profileImage: string;
  token?: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private registerUrl = 'http://localhost:8880/api/auth/register';
  private loginUrl = 'http://localhost:8880/api/auth/login';

  private readonly USER_KEY = 'currentUser';

  constructor(private http: HttpClient, private router: Router) {}

  login(userData: LoginData): Observable<User> {
    return this.http.post<any>(this.loginUrl, userData).pipe(
      map(response => {
        // response.user y response.token vienen del backend
        const user: User = response.user;
        user.token = response.token;
        return user;
      }),
      tap(user => {
        console.log('Login exitoso', user);
        if (user && user.token) {
          // Guarda todo el user (incluido el token) en localStorage
          localStorage.setItem(this.USER_KEY, JSON.stringify(user));
        }
      })
    );
  }

  register(userData: RegisterData): Observable<any> {
    return this.http.post(this.registerUrl, userData);
  }

  logout() {
    localStorage.removeItem(this.USER_KEY);
    this.router.navigate(['']);
  }

  getCurrentUser(): User | null {
    const userJson = localStorage.getItem(this.USER_KEY);
    if (!userJson) {
      return null;
    }
    return JSON.parse(userJson) as User;
  }

  isLoggedIn(): boolean {
    return !!this.getCurrentUser();
  }

  /** Nuevo: devuelve solo el string JWT, o null si no hay ninguno */
  getToken(): string | null {
    const u = this.getCurrentUser();
    return u && u.token ? u.token : null;
  }
}