import { Routes } from '@angular/router';
import { LandingComponent } from './modules/landing/landing.component';
import {HomeComponent} from "./modules/home/home.component";

export const routes: Routes = [
    { path: '', component: LandingComponent},
    {path: 'home', component: HomeComponent}
];
