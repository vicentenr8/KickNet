import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-home', // Selector corregido
  standalone: true,
  imports: [CommonModule], // Importa CommonModule
  templateUrl: './home.component.html', // Apunta a su propio HTML
  //styleUrl: './landing.component.css' // Opcional: si tiene estilos
})

export class HomeComponent {
  
}