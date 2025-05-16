import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms'; // Importa FormsModule para usar [(ngModel)]
import { FixturesService } from '../fixtures/fixtures.service';
import { FixturesComponent } from "../fixtures/fixtures.component";
@Component({
  selector: 'app-home', // Selector corregido
  standalone: true,
  imports: [CommonModule, FormsModule, FixturesComponent], // Importa CommonModule
  templateUrl: './home.component.html', // Apunta a su propio HTML
  //styleUrl: './landing.component.css' // Opcional: si tiene estilos
})

export class HomeComponent {
  mensagges: string = '';
  publications: Publication[] = []
  user = {
    name: 'Xisco',
    username: 'Xisco1999',
    profileImage: 'X(isco).jpeg'
  };
  publish = () => {
    if (this.mensagges.trim()) {
      this.publications.unshift({
        text: this.mensagges,
        date: new Date(),
        name: this.user.name,
        username: this.user.username,
        profileImage: this.user.profileImage
      });
      // Reinicia el campo de texto después de publicar
      this.mensagges = '';
    }
  }

    tiempoTranscurrido(date: Date): string {
      const now = new Date();
      const diference = now.getTime() - new Date(date).getTime(); // en ms
      const seconds = Math.floor(diference / 1000);
  
      if (seconds < 60) {
        return 'Justo Ahora';
      } else if (seconds < 3600) {
        const minutes = Math.floor(seconds / 60);
        return minutes + (minutes === 1 ? ' minuto' : ' minutos');
      } else if (seconds < 86400) {
        const hours = Math.floor(seconds / 3600);
        return hours + (hours === 1 ? ' hora' : ' horas');
      } else {
        const days = Math.floor(seconds / 86400);
        return days + (days === 1 ? ' día' : ' días');
      }
    }
  }
  

interface Publication {
  text: string;
  date: Date;
  name: string;
  username: string;
  profileImage: string;
}


