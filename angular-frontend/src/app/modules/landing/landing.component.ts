import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';  // Importa Router
import { AuthService } from './auth.service';
import { HttpClient } from '@angular/common/http';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-landing',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './landing.component.html',
})
export class LandingComponent {
  isModalOpen = false;
  isSignUpModalOpen = false;

  loginEmail = '';
  loginPassword = '';
  registerEmail = '';
  registerPassword = '';
  registerUsername = '';
  registerConfirmPassword = '';

  constructor(private authService: AuthService, private router: Router, private http: HttpClient) {} 
  openModal() {
    this.isModalOpen = true;
  }

  closeModal() {
    this.isModalOpen = false;
  }

  openSignUpModal() {
    this.isSignUpModalOpen = true;
    this.isModalOpen = false;
  }

  closeSignUpModal() {
    this.isSignUpModalOpen = false;
  }

  login() {
    this.authService.login({ email: this.loginEmail, password: this.loginPassword })
      .subscribe({
        next: (res) => {
          alert('Login exitoso');
          console.log('Login exitoso', res);
          localStorage.setItem('token', res.token ?? '');  // Guardar token
          this.closeModal();
          this.router.navigate(['/home']);  // Redirigir a /home
        },
        error: (err) => {
          alert('Error en el login');
          console.error('Error en login', err);
        }
      });
  }

  register() {
    if (this.registerPassword !== this.registerConfirmPassword) {
      alert('Las contraseñas no coinciden');
      return;
    }
  
    const userData = {
      username: this.registerUsername,
      email: this.registerEmail,
      password: this.registerPassword
    };
  
    this.authService.register(userData).subscribe({
      next: res => {
        console.log('Registro OK:', res);
        this.closeSignUpModal();
      },
      error: err => {
        console.error('Error en registro:', err);
        alert(err.error?.error || 'Error desconocido en registro');
      }
    });
  }
  
}
