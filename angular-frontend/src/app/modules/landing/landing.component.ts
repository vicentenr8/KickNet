import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';  // Importa Router
import { AuthService } from './auth.service';
import { HttpClient } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { ToastrModule, ToastrService } from 'ngx-toastr';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';

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

  constructor(private authService: AuthService, private router: Router, private http: HttpClient, private toastr: ToastrService) {} 
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
          this.toastr.success('Login exitoso');
          localStorage.setItem('token', res.token ?? '');  // Guardar token
          this.closeModal();
          this.router.navigate(['/home']);  // Redirigir a /home
        },
        error: (err) => {
          //contraseña incoorecta
          if(this.loginPassword && err.status === 401) {
            this.toastr.error('Contraseña incorrecta');
            return;
          }
          // email no registrado
          if(this.loginEmail && err.status === 404) {
            this.toastr.error('Email no registrado');
            return;
          }
          console.error('Error en login', err);
        }
      });
  }

  register() {
    if (this.registerPassword !== this.registerConfirmPassword) {
      this.toastr.error('Las contraseñas no coinciden');
      return;
    }
  
    const userData = {
      username: this.registerUsername,
      email: this.registerEmail,
      password: this.registerPassword,
    };
  
    this.authService.register(userData).subscribe({
      next: res => {
        this.toastr.info('Registro exitoso, verifica tu correo');
        this.closeSignUpModal();
      },
      error: err => {
        console.error('Error en registro:', err);
        if (err.status === 400) {
          this.toastr.error('Error en el registro, verifica tus datos');
        } else {
          this.toastr.error('Error en el registro, inténtalo de nuevo más tarde');
        }
      }
    });
  }
  
}
