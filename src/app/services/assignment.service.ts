import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Request } from './observables/request';
import { environment } from '../../environments/environment';
import { LoaderService } from './loader/loader.service';

@Injectable()
export class AssignmentService {

  public statusList = [
    'AWAITING ADMIN',
    'TICKETS READY FOR DELIVERY',
    'TICKETS DELIVERED',
    'CANCELLED'
  ];

  private getTicketAssignmentsURL = environment.backendPath + 'getAllTicketAssignments.php';
  private cancelTicketAssignmentURL = environment.backendPath + 'cancelTicketAssignment.php';
  private transferTicketAssignmentURL = environment.backendPath + 'transferTicketAssignment.php';
  private updateTicketAssignmentURL = environment.backendPath + 'updateTicketAssignment.php';
  private updateTicketAssignmentStatusURL = environment.backendPath + 'updateTicketAssignmentStatus.php';

  ticketAssignments = [];
  ticketAssignmentEvents = [];
  ticketAssignmentStatuses = [];

  ticketAssignmentsForReporting = [];

  constructor(private http: HttpClient, private loader: LoaderService) { }

  getTicketAssignments() {
    this.loader.show();
    this.http.get<Request>(this.getTicketAssignmentsURL + "?tsp=" + new Date()).subscribe(
      data => this.fetchTicketAssignmentData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    );
  }

  private fetchTicketAssignmentData(data): void {
    this.ticketAssignments = data;
    //console.log(data)
    this.filterEventsFromAssignments(data, () => {
      this.loader.hide();
    });
  }

  getTicketAssignmentsForReporting(fromDate, toDate, reasonCode) {
    this.loader.show();
    this.http.get<Request>(this.getTicketAssignmentsURL + "?tsp=" + new Date()).subscribe(
      data => this.fetchTicketAssignmentDataForReporting(data, fromDate, toDate, reasonCode),
      err => {
        console.error(err);
        this.loader.hide();
      }
    );
  }

  private fetchTicketAssignmentDataForReporting(data, fromDate, toDate, reasonCode): void {
    //this.ticketAssignmentsForReporting = data;
    //console.log(fromDate.getTime());
    //console.log(toDate.getTime());
    // for(let i = 0; i < data.length; i++){
    //   let eventDateTime = new Date(data[i].EventDateTime.date).getTime();
    //   if(eventDateTime >= new Date(fromDate).getTime() && eventDateTime <= new Date(toDate).getTime()){
    //console.log(data[i])
    //   }
    // }

    if(reasonCode){
      this.ticketAssignmentsForReporting =
      data.filter(x =>
        new Date(x.EventDateTime.date).getTime() > new Date(fromDate).getTime() &&
        new Date(x.EventDateTime.date).getTime() < new Date(toDate).getTime() &&
        x.AssociatedReasonCode === reasonCode);
    }
    else{
      this.ticketAssignmentsForReporting =
      data.filter(x =>
        new Date(x.EventDateTime.date).getTime() > new Date(fromDate).getTime() &&
        new Date(x.EventDateTime.date).getTime() < new Date(toDate).getTime());
    }

    this.filterEventsFromAssignments(data, () => {
      this.loader.hide();
    });
  }

  private filterEventsFromAssignments(assignments, callback) {
    this.ticketAssignmentEvents = assignments.filter(
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

  updateTicketAssignment(updatedTicketAssignment) {
    this.loader.show();
    return this.http.post<any>(this.updateTicketAssignmentURL, JSON.stringify(updatedTicketAssignment));
  }

  updateTicketAssignmentStatus(updatedTicketAssignmentIndex) {
    this.loader.show();
    return this.http.post<any>(this.updateTicketAssignmentStatusURL, JSON.stringify(this.ticketAssignments[updatedTicketAssignmentIndex]));
  }

  cancelTicketAssignment(ticketAssignmentToCancelIndex) {
    this.loader.show();
    return this.http.post(this.cancelTicketAssignmentURL, JSON.stringify(this.ticketAssignments[ticketAssignmentToCancelIndex]));
  }

  transferTicketAssignment(transferedTicket) {
    this.loader.show();
    return this.http.post(this.transferTicketAssignmentURL, JSON.stringify(transferedTicket));
  }
}
