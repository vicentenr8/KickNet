import { Component, OnInit } from '@angular/core';

import { Task } from '../../shared/interfaces/Task';
import { CommonModule } from '@angular/common';
import { TaskService } from '../../shared/services/task-service.service';


@Component({
  selector: 'app-tasks',
  imports: [CommonModule],
  templateUrl: './tasks.component.html',
  styleUrls: ['./tasks.component.scss']
})
export class TasksComponent implements OnInit {
  tasks: Task[] = [];

  constructor(private taskService: TaskService) {}

  ngOnInit(): void {
    this.loadTasks();
  }

  loadTasks(): void {
    this.taskService.getTasks().subscribe(
      (tasks) => this.tasks = tasks,
      (error) => console.error('Error fetching tasks:', error)
    );
  }

  addTask(title: string): void {
    if (!title.trim()) return;
    const newTask: Task = { id: 0, title, completed: false };
    this.taskService.addTask(newTask).subscribe(() => this.loadTasks());
  }
  

  toggleTask(task: Task): void {
    task.completed = !task.completed;
    this.taskService.updateTask(task).subscribe(() => this.loadTasks());
  }

  deleteTask(id: number): void {
    this.taskService.deleteTask(id).subscribe(() => this.loadTasks());
  }
}
