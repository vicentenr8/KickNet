import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable} from 'rxjs';
import {map, tap} from 'rxjs/operators';

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
  token?: string; // si usas token JWT, por ejemplo
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private registerUrl = 'http://localhost:8880/api/auth/register';
  private loginUrl = 'http://localhost:8880/api/auth/login';

  private readonly USER_KEY = 'currentUser';
   private readonly TOKEN_KEY = 'token'

  constructor(private http: HttpClient) {}

  login(userData: LoginData): Observable<User> {
    return this.http.post<any>(this.loginUrl, userData).pipe(
      map(response => {
        // El user viene dentro de response.user, y el token en response.token
        const user: User = response.user;
        user.token = response.token;
        return user;
      }),
      tap(user => {
        console.log('Login exitoso', user);
        if (user) {
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
  }

  getCurrentUser(): User | null {
    const userJson = localStorage.getItem(this.USER_KEY);
    if (!userJson) return null;
    return JSON.parse(userJson) as User;
  }

  isLoggedIn(): boolean {
    return !!this.getCurrentUser();
  }
}
