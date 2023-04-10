import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Request } from './observables/request';
import { environment } from '../../environments/environment';
import { LoaderService } from './loader/loader.service';

@Injectable()
export class RequestService {

  public statusList = [
    'APPROVED',
    'SUBMITTED/PENDING',
    'NOT APPROVED',
    'CANCELLED'
  ];

  private getTicketRequestsURL = environment.backendPath + 'getAllTicketRequestsWithTicketDetail.php';
  private addTicketRequestURL = environment.backendPath + 'addTicketRequest.php';
  private approveTicketRequestURL = environment.backendPath + 'approveTicketRequest.php';
  private declineTicketRequestURL = environment.backendPath + 'declineTicketRequest.php';
  private cancelTicketRequestURL = environment.backendPath + 'cancelTicketRequest.php';
  private updateTicketRequestURL = environment.backendPath + 'updateTicketRequest.php';

  ticketRequests = [];
  ticketRequestEvents = [];

  constructor(private http: HttpClient, private loader: LoaderService) { }

  getTicketRequests(): void {
    this.loader.show();
    this.http.get<Request>(this.getTicketRequestsURL + "?tsp=" + new Date()).subscribe(
      data => this.fetchTicketRequestData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    );
  }

  private fetchTicketRequestData(data): void {
    this.ticketRequests = data;
    this.filterEventsFromRequests(data, () => {
      this.loader.hide();
    });
  }

  private filterEventsFromRequests(requests, callback) {
    this.ticketRequestEvents = requests.filter(
      (value, index, self) => self.map(
        (e) => {
          return e.EventNumber
        }
      ).indexOf(value.EventNumber) === index
    ).map(e => {
      return {
        EventNumber: e.EventNumber,
        EventDescription: e.EventDescription,
        EventDateTime: e.EventDateTime
      }
    });
    callback();
  }

  updateTicketRequest(updatedRequest) {
    this.loader.show();
    // Set second request number field because of ...?
    updatedRequest.AssociatedTicketRequestNumber = updatedRequest.RequestNumber;
    return this.http.post<any>(this.updateTicketRequestURL, JSON.stringify(updatedRequest));
  }

  addTicketRequest(newTicketRequest: string) {
    this.loader.show();
    return this.http.post<any>(this.addTicketRequestURL, JSON.stringify(newTicketRequest));
  }

  approveTicketRequest(ticketRequestToCancelIndex) {
    this.loader.show();
    return this.http.post(this.approveTicketRequestURL, JSON.stringify(this.ticketRequests[ticketRequestToCancelIndex]));
  }

  declineTicketRequest(ticketRequestToCancelIndex) {
    this.loader.show();
    return this.http.post(this.declineTicketRequestURL, JSON.stringify(this.ticketRequests[ticketRequestToCancelIndex]));
  }

  cancelTicketRequest(ticketRequestToCancelIndex) {
    this.loader.show();
    return this.http.post(this.cancelTicketRequestURL, JSON.stringify(this.ticketRequests[ticketRequestToCancelIndex]));
  }
}
