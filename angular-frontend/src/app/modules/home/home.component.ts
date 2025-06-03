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
  selectedImage: string | undefined = undefined;
  publications: Publication[] = [];

  user = {
    name: 'Visco',
    username: 'Visco1999',
    profileImage: '' // Si está vacío, se usa la inicial
  };

  avatarColors = [
    '#1abc9c', '#3498db', '#9b59b6',
    '#e67e22', '#e74c3c', '#2ecc71', '#f1c40f'
  ];

  publish = () => {
    if (this.mensagges.trim()) {
      this.publications.unshift({
        text: this.mensagges,
        date: new Date(),
        name: this.user.name,
        username: this.user.username,
        profileImage: this.user.profileImage,
        image: this.selectedImage ?? undefined
      });
      this.mensagges = '';
      this.selectedImage = undefined;
    }
  };

  tiempoTranscurrido(date: Date): string {
    const now = new Date();
    const seconds = Math.floor((now.getTime() - new Date(date).getTime()) / 1000);
    if (seconds < 60) return 'Justo ahora';
    else if (seconds < 3600) return `${Math.floor(seconds / 60)} min`;
    else if (seconds < 86400) return `${Math.floor(seconds / 3600)} h`;
    else return `${Math.floor(seconds / 86400)} d`;
  }

  onImageSelected(event: any) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = () => this.selectedImage = reader.result as string;
      reader.readAsDataURL(file);
    }
  }

  get characterCount(): number {
    return this.mensagges.length;
  }

  get userInitial(): string {
    return this.user.username.charAt(0).toUpperCase();
  }

  getAvatarColor(username: string): string {
    let hash = 0;
    for (let i = 0; i < username.length; i++) {
      hash = username.charCodeAt(i) + ((hash << 5) - hash);
    }
    const index = Math.abs(hash) % this.avatarColors.length;
    return this.avatarColors[index];
  }
}

interface Publication {
  text: string;
  date: Date;
  name: string;
  username: string;
  profileImage: string;
  image?: string;
}