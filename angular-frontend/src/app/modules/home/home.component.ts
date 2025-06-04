import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { FixturesComponent } from '../fixtures/fixtures.component';
import { PublicationService } from './Publication.service';
import { AuthService, User } from '../landing/auth.service';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, FormsModule, FixturesComponent],
  templateUrl: './home.component.html',
})
export class HomeComponent {
  mensagges: string = '';
  selectedImage?: string;
  publications: Publication[] = [];

  user: User | null = null;

  avatarColors = [
    '#1abc9c',
    '#3498db',
    '#9b59b6',
    '#e67e22',
    '#e74c3c',
    '#2ecc71',
    '#f1c40f',
  ];

  constructor(
    private publicationService: PublicationService,
    private authService: AuthService,
    private http: HttpClient
  ) {
    // Cargar usuario actual de AuthService (localStorage)
    this.user = this.authService.getCurrentUser();
  }

  publish() {
    const token = localStorage.getItem('token');
    if (!token) {
      alert('No tienes token JWT, por favor inicia sesión');
      return;
    }

    if (!this.mensagges.trim()) return;
    if (!this.user) {
      console.error('Usuario no autenticado');
      alert('Debes iniciar sesión para publicar.'); // Mejor un alert si es un error crítico
      return;
    }
    const payload = {
      user_id: this.user.id,
      content: this.mensagges,
      image: this.selectedImage ? this.selectedImage : null, // Asegúrate de que la imagen sea opcional
    };
    if (this.selectedImage) {
      payload['image'] = this.selectedImage; // Añadir la imagen al payload si está seleccionada
    }
    this.publicationService.createPublication(payload).subscribe({
      next: (response: any) => {
        console.log('Publicación creada:', response);
        this.publications.unshift({
          text: this.mensagges,
          date: new Date(),
          email: this.user!.email,
          username: this.user!.username,
          image: this.selectedImage,
        });
        this.mensagges = '';
        this.selectedImage = undefined;
      },
      error: (err) => {
        console.error('Error al publicar', err);
        // Puedes añadir lógica para manejar 401 aquí, por ejemplo:
        if (err.status === 401) {
          alert(
            'Tu sesión ha expirado o no estás autorizado. Por favor, inicia sesión de nuevo.'
          );
          // this.authService.logout(); // Opcional: desloguear al usuario
          // this.router.navigate(['/login']); // Opcional: redirigir al login
        } else {
          alert('Hubo un error al publicar. Inténtalo de nuevo.');
        }
      },
    });
  }

  ngOnInit() {
    this.loadPublications(); // Llama a la función para cargar las publicaciones
  }

  // Método para cargar las publicaciones desde el servicio
  loadPublications() {
    this.publicationService.getPublications().subscribe({
      next: (response: any) => {
        console.log('Publicaciones cargadas desde el backend:', response); // Para depuración

        // Mapea la respuesta a tu interfaz Publication
        this.publications = response.map((pub: any) => ({
          text: pub.content, // Contenido de la publicación
          date: new Date(pub.date), // Fecha de creación
          email: pub.user?.email || 'email@desconocido.com',
          username: pub.user?.username || 'Usuario Desconocido',
          image: pub.user?.profileImage || undefined, // Imagen de perfil del usuario (puede ser undefined)
        }));
      },
      error: (err) => {
        console.error('Error al cargar publicaciones:', err);
        // Manejo de errores para el usuario
        if (err.status === 401) {
          alert('Tu sesión ha expirado o no estás autorizado para ver las publicaciones. Por favor, inicia sesión de nuevo.');
          // Opcional: redirigir al login o desloguear al usuario
          // this.authService.logout();
          // this.router.navigate(['/login']);
        } else {
          alert('Hubo un error al cargar las publicaciones. Inténtalo de nuevo más tarde.');
        }
      },
    });
  }

  tiempoTranscurrido(date: Date): string {
    const now = new Date();
    const seconds = Math.floor(
      (now.getTime() - new Date(date).getTime()) / 1000
    );
    if (seconds < 60) return 'Justo ahora';
    else if (seconds < 3600) return `${Math.floor(seconds / 60)} min`;
    else if (seconds < 86400) return `${Math.floor(seconds / 3600)} h`;
    else return `${Math.floor(seconds / 86400)} d`;
  }

  onImageSelected(event: any) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = () => (this.selectedImage = reader.result as string);
      reader.readAsDataURL(file);
    }
  }

  get characterCount(): number {
    return this.mensagges.length;
  }

  get userInitial(): string {
    if (!this.user?.username) return '';
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
  email: string;
  username: string;
  image?: string;
}
