import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { FixturesComponent } from '../fixtures/fixtures.component';
import { PublicationService } from './Publication.service';
import { AuthService, User } from '../landing/auth.service';
import { HttpClient } from '@angular/common/http';
import { CommentService } from './Comment.service';
import { ToastrService } from 'ngx-toastr';

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
  activatioSection: string = 'feed';
  isLiked = false;
  isLikedMap: { [publicationId: number]: boolean } = {};

  toggleLike(pubId: number) {
    this.isLikedMap[pubId] = !this.isLikedMap[pubId];
  }
  user: User | null = null;
  userId: number = 0;
  avatarGenerator = new AvatarGenerator();
  commentsCountMap: { [publicationId: number]: number } = {};

  showCommentModal = false;
  selectedPublication: Publication | null = null;
  newCommentContent: string = '';
  commentsForSelectedPublication: Comment[] = [];

  constructor(
    private publicationService: PublicationService,
    private authService: AuthService,
    private http: HttpClient,
    private commentService: CommentService,
    private toastr: ToastrService
  ) {
  }

  ngOnInit() {
    this.user = this.authService.getCurrentUser();
    this.loadPublications();
    if (this.user) {
      this.userId = this.user.id;
    }
    if (!this.isDesktop()) {
      this.mobileView = 'feed';
    }
  }

  ngAfterViewInit(): void {
    window.addEventListener('resize', () => {
      if (!this.isDesktop() && !this.mobileView) {
        this.mobileView = 'feed';
      }
    });
  }

  publish() {
    const token = localStorage.getItem('token');
    if (!token) {
      this.toastr.error('Debes iniciar sesión para publicar.');
      return;
    }

    if (!this.mensagges.trim()) return;
    if (!this.user) {
      this.toastr.error('Debes iniciar sesión para publicar.');
      return;
    }

    const payload = {
      user_id: this.user.id,
      content: this.mensagges,
      image: this.selectedImage ? this.selectedImage : null,
    };

    this.publicationService.createPublication(payload).subscribe({
      next: (response: any) => {
        this.toastr.success('Publicación creada exitosamente');
        const imagenTemporal = this.selectedImage;
        this.selectedImage = undefined;
        this.publications.unshift({
          text: this.mensagges,
          date: new Date(),
          email: this.user!.email,
          username: this.user!.username,
          image: imagenTemporal,
          user_id: this.user!.id,
        });
        this.mensagges = '';
      },
      error: (err) => {
        this.toastr.error('Error al crear la publicación');
        if (err.status === 401) {
          this.toastr.error(
            'Tu sesión ha expirado o no estás autorizado. Por favor, inicia sesión de nuevo.'
          );
          localStorage.removeItem('token');
          this.authService.logout();
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

        this.publications.forEach((pub) => {
          if (pub.id) {
            this.loadCommentsCount(pub.id);
          }
        });
      },
      error: () => {
        this.toastr.error('Error al cargar las publicaciones');
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
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
      this.selectedImage = reader.result as string;
      console.log('Imagen seleccionada:', this.selectedImage);
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

  openCommentModal(pub: Publication): void {
    this.showCommentModal = true;
    this.selectedPublication = pub;
    this.loadComments(pub.id!);
  }

  closeCommentModal(): void {
    this.showCommentModal = false;
    this.selectedPublication = null;
    this.newCommentContent = '';
    this.commentsForSelectedPublication = [];
  }

  loadComments(publicationId: number): void {
    this.commentService.getComments(publicationId).subscribe({
      next: (comments: any) => {
        console.log('Comentarios recibidos:', comments);
        this.commentsForSelectedPublication = comments;
      },
      error: (err) => {
        this.toastr.error('Error al cargar comentarios');
        console.error('Error al cargar comentarios', err);
      },
    });
  }

  publicarComentario(): void {
    if (!this.selectedPublication) {
      this.toastr.error('No hay publicación seleccionada para comentar');
      return;
    }
    const trimmedText = this.newCommentContent.trim();
    if (!trimmedText) {
      this.toastr.warning('El comentario no puede estar vacío');
      return;
    }

    this.commentService
      .createComment({
        content: this.newCommentContent,
        user_id: this.userId,
        publication_id: this.selectedPublication.id!,
      })
      .subscribe({
        next: () => {
          this.toastr.success('Comentario publicado exitosamente');
          this.newCommentContent = '';
          this.loadCommentsCount(this.selectedPublication!.id!);
          this.loadComments(this.selectedPublication!.id!); // recarga comentarios para mostrar el nuevo
        },
        error: (err) => {
          this.toastr.error('Error al publicar el comentario');
          console.error('Error al publicar comentario', err);
        },
      });
  }

  loadCommentsCount(publicationId: number) {
    this.commentService.countComments(publicationId).subscribe({
      next: (res) => {
        this.commentsCountMap[publicationId] = res.comments_count;
      },
      error: (err) => {
        console.error('Error al obtener contador de comentarios', err);
        this.commentsCountMap[publicationId] = 0;
      },
    });
  }

  eliminarPublicacion(publicationId: number) {
    if (!confirm('¿Seguro que quieres eliminar esta publicación?')) return;

    this.publicationService.deletePublication(publicationId).subscribe({
      next: () => {
        this.toastr.success('Publicación eliminada exitosamente');
        this.publications = this.publications.filter(
          (p) => p.id !== publicationId
        );
      },
      error: () => {
        this.toastr.error('Error al eliminar la publicación');
      },
    });
  }

  logout() {
    this.authService.logout();
    this.toastr.success('Has cerrado sesión exitosamente');
  }

  // Variable para controlar qué sección mostrar en móvil
  mobileView: 'feed' | 'left' | 'right' = 'feed';

  // Métodos para cambiar vista móvil
  showFeed() {
    this.mobileView = 'feed';
    this.activatioSection = 'feed'; // Cambia la sección activa a 'feed'
  }

  showLeft() {
    this.mobileView = 'left';
    this.activatioSection = 'left'; // Cambia la sección activa a 'left'
  }

  showRight() {
    this.mobileView = 'right';
    this.activatioSection = 'right'; // Cambia la sección activa a 'right'
  }

  isDesktop(): boolean {
    return window.innerWidth >= 1024;
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
    return this.avatarColors[absHash % this.avatarColors.length];
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

interface Comment {
  id: number;
  publicationId: number;
  userId: number;
  content: string;
  date: Date; // o Date
  username: string; // <- asegúrate de que esto exista
  email: string; // <- asegúrate de que esto exista
}
