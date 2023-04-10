import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { LoaderState } from './loaderState';

@Injectable()
export class LoaderService {

  private loaderSubject = new Subject<LoaderState>();
  private activeLoaders = 0;

  loaderState = this.loaderSubject.asObservable();

  constructor() { }

  show() {
    this.activeLoaders++;
    this.setNewLoaderState();
    //console.log(`Added loader (${this.activeLoaders})`);
  }
  hide() {
    this.activeLoaders--;
    this.setNewLoaderState();
    //console.log(`Removed loader (${this.activeLoaders})`)
  }

  private setNewLoaderState() {
    if (this.activeLoaders == 0) {
      this.loaderSubject.next(<LoaderState>{ show: false });
    }
    else {
      this.loaderSubject.next(<LoaderState>{ show: true });
    }
  }
}
