import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-landing', // Selector corregido
  standalone: true,
  imports: [CommonModule], // Importa CommonModule
  templateUrl: './landing.component.html', // Apunta a su propio HTML
  //styleUrl: './landing.component.css' // Opcional: si tiene estilos
})
export class LandingComponent {
  isModalOpen = false;
  isSignUpModalOpen = false;

  openModal() {
    this.isModalOpen = true;
  }

  closeModal() {
    this.isModalOpen = false;
  }

  openSignUpModal() {
    this.isSignUpModalOpen = true;
    this.isModalOpen = false; // Cierra el modal de inicio de sesión
  }

  closeSignUpModal() {
    this.isSignUpModalOpen = false;
  }
}