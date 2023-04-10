import { Injectable } from '@angular/core';
import { environment } from '../../environments/environment';
import { HttpClient } from '@angular/common/http';
import { LoaderService } from './loader/loader.service';

@Injectable()
export class VenueService {

  private httpUrlGetEventVenues = environment.backendPath + 'getAllEventVenues.php';

  public venues = [];

  constructor(private http: HttpClient, private loader: LoaderService) { }

  getAllEventVenues(): void {
    this.loader.show();
    this.http.get<any>(this.httpUrlGetEventVenues + '?tsp=' + new Date()).subscribe(
      data => {
        this.venues = data;
        this.loader.hide();
      },
      err => {
        console.error(err);
        this.loader.hide();
      }
    );
  }

  getVenueName(venueNumber) {
    const i = this.venues.findIndex(
      v => v.VenueNumber === venueNumber);
    if (i > -1) {
      return this.venues[i].Name;
    }
    return null;
  }
}
