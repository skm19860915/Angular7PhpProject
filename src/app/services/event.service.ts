import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Event } from './observables/event';
import { environment } from '../../environments/environment';
import { Category } from './observables/category';
import { LoaderService } from './loader/loader.service';

@Injectable()
export class EventService {

  public statusList = [
    'AVAILABLE',
    'NOT AVAILABLE'
  ];

  private httpUrlGetEvents = environment.backendPath + 'getAllEventsWithVenueInformation.php';
  private httpUrlAddEvent = environment.backendPath + 'addEvent.php';
  private httpUrlDeleteEvent = environment.backendPath + 'deleteEvent.php';
  private httpUrlUpdateEvent = environment.backendPath + 'updateEvent.php';
  private httpUrlGetCategories = environment.backendPath + 'getAllTicketCategories.php';
  private urlAddEventsOfTemplate = environment.backendPath + 'addEventsOfTemplate.php';

  public events = [];
  public eventCategories = [];
  public eventCategoriesFilled = [];

  constructor(private http: HttpClient, private loader: LoaderService) { }

  getEventsWithVenueInfo() {
    this.loader.show();
    this.http.get<Event>(this.httpUrlGetEvents + '?tsp=' + new Date()).subscribe(
      data => this.fetchEventData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    );
  }

  returnEventsWithVenueInfo() {
    this.loader.show();
    return this.http.get<Event>(this.httpUrlGetEvents + '?tsp=' + new Date());
  }

  private fetchEventData(data) {
    data.forEach(event => {
      if (event.Status === 'AVAILABLE') {
        if (this.eventCategoriesFilled.indexOf(event['AssociatedTicketCategory'].trim()) === -1) {
          this.eventCategoriesFilled.push(event['AssociatedTicketCategory'].trim());
        }
      }
    });
    this.events = data;
    this.loader.hide();
  }

  addEvent(newEvent) {
    //console.log("post....", newEvent);
    this.loader.show();
    return this.http.post(this.httpUrlAddEvent, JSON.stringify(newEvent));
  }

  updateEvent(updatedEventObj) {
    this.loader.show();
    return this.http.post(this.httpUrlUpdateEvent, JSON.stringify(updatedEventObj));
  }

  deleteEvent(index) {
    this.loader.show();
    return this.http.post(this.httpUrlDeleteEvent, JSON.stringify(this.events[index]));
  }

  getEventCategories() {
    this.http.get<Category>(this.httpUrlGetCategories + '?tsp=' + new Date()).subscribe(
      data => this.fetchCategoryData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    );
  }

  private fetchCategoryData(data): void {
    this.eventCategories = data;
  }

  addNewEvents(eventObj){
    this.loader.show();
    return this.http.post(this.urlAddEventsOfTemplate, JSON.stringify(eventObj));
  }
}
