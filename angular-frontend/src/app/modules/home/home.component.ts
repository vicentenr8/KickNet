import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { FixturesComponent } from '../fixtures/fixtures.component';
import { PublicationService } from './Publication.service';
import { AuthService, User } from '../landing/auth.service';
import { HttpClient } from '@angular/common/http';
import { CommentService } from './Comment.service'; 

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, FormsModule, FixturesComponent],
  templateUrl: './home.component.html',
})
export class HomeComponent implements OnInit {
  mensagges: string = '';
  selectedImage?: string;
  publications: Publication[] = [];

  user: User | null = null;
  userId: number = 0;
  avatarGenerator = new AvatarGenerator();
  commentsCountMap: { [publicationId: number]: number } = {};

  showCommentModal = false;
  selectedPublication: Publication | null = null;
  newCommentContent: string = '';


  constructor(
    private publicationService: PublicationService,
    private authService: AuthService,
    private http: HttpClient,
    private commentService: CommentService 
  ) {
    this.user = this.authService.getCurrentUser();
  }

  ngOnInit() {
    this.loadPublications();
    if (this.user) {
      this.userId = this.user.id;
    }
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
      alert('Debes iniciar sesión para publicar.');
      return;
    }

    const payload = {
      user_id: this.user.id,
      content: this.mensagges,
      image: this.selectedImage ? this.selectedImage : null,  // Mantenemos por backend aunque no usemos en frontend
    };

    this.publicationService.createPublication(payload).subscribe({
      next: (response: any) => {
        console.log('Publicación creada:', response);
        this.publications.unshift({
          text: this.mensagges,
          date: new Date(),
          email: this.user!.email,
          username: this.user!.username,
          image: this.selectedImage,
          user_id: this.user!.id,
        });
        this.mensagges = '';
      
      },
      error: (err) => {
        console.error('Error al publicar', err);
        if (err.status === 401) {
          alert('Tu sesión ha expirado o no estás autorizado. Por favor, inicia sesión de nuevo.');
        } else {
          alert('Hubo un error al publicar. Inténtalo de nuevo.');
        }
      },
    });
  }
  
  loadPublications() {
    this.publicationService.getPublications().subscribe({
      next: (response: any) => {
        this.publications = response.map((pub: any) => ({
          text: pub.content,
          date: new Date(pub.date),
          email: pub.email || 'email@desconocido.com',
          username: pub.username || 'Usuario Desconocido',
          image: pub.image || '',
          user_id: pub.user_id,
          id: pub.id,
        }));
  
        // Aquí llamas a loadCommentsCount para cada publicación:
        this.publications.forEach(pub => {
          if (pub.id) {
            this.loadCommentsCount(pub.id);
          }
        });
      },
      error: (err) => {
        console.error('Error al cargar publicaciones:', err);
      },
    });
  }
  

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
    if (!file) return;
  
    const reader = new FileReader();
    reader.onload = () => {
      this.selectedImage = reader.result as string;
  
      // Guardar la imagen en localStorage
      localStorage.setItem('imagenTemporal', this.selectedImage);
    };
    reader.readAsDataURL(file);
  }

  get characterCount(): number {
    return this.mensagges.length;
  }

  get userInitial(): string {
    if (!this.user?.username) return '';
    return this.user.username.charAt(0).toUpperCase();
  }

  getAvatarColor(username: string): string {
    return this.avatarGenerator.getAvatarColor(username);
  }

  oppenCommentModal(pub: Publication): void {
    this.showCommentModal = true;
    this.selectedPublication = pub;
  }

  closeCommentModal(): void {
    this.showCommentModal = false;
    this.selectedPublication = null;
    this.newCommentContent = '';
  }

  publicarComentario(): void {
    if (!this.newCommentContent.trim() || !this.selectedPublication) return;
  
    this.commentService.createComment({
      content: this.newCommentContent,
      user_id: this.userId,
      publication_id: this.selectedPublication.id!
    }).subscribe({
      next: (res) => {
        console.log('Comentario publicado:', res);
        this.newCommentContent = '';
        this.loadCommentsCount(this.selectedPublication!.id!);
        this.closeCommentModal();
      },
      error: (err) => {
        console.error('Error al publicar comentario:', err);
      }
    });
  }
  

  loadCommentsCount(publicationId: number) {
    this.commentService.countComments(publicationId).subscribe({
      next: (res) => {
        this.commentsCountMap[publicationId] = res.comments_count;
      },
      error: (err) => {
        console.error('Error al obtener contador de comentarios', err);
        this.commentsCountMap[publicationId] = 0; // fallback
      },
    });
  }

  eliminarPublicacion(publicationId: number) {
    const confirmado = confirm('¿Seguro que quieres eliminar esta publicación?');
    if (!confirmado) return;
  
    this.publicationService.deletePublication(publicationId).subscribe({
      next: () => {
        this.publications = this.publications.filter(p => p.id !== publicationId);
      },
      error: (err) => {
        console.error('Error eliminando publicación', err);
      }
    });
  }

  logout() {
    this.authService.logout();
  }
  
  
}

class AvatarGenerator {
  avatarColors = [
    '#1abc9c',
    '#3498db',
    '#9b59b6',
    '#e67e22',
    '#e74c3c',
    '#2ecc71',
    '#f1c40f',
  ];

  getAvatarColor(username: string): string {
    let hash = 0;
    for (let i = 0; i < username.length; i++) {
      hash = username.charCodeAt(i) + ((hash << 5) - hash);
    }
    const absHash = Math.abs(hash);
    const index = absHash % this.avatarColors.length;
    return this.avatarColors[index];
  }
}

interface Publication {
  id?: number;
  text: string;
  date: Date;
  email: string;
  username: string;
  image?: string;
  user_id?: number;
}
