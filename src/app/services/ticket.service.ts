import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Ticket } from './observables/ticket';
import { UsageReason } from './observables/usageReason';
import { SeatType } from './observables/seatType';
import { environment } from '../../environments/environment';
import { LoaderService } from './loader/loader.service';
import { TicketDeliveryType } from './observables/ticketDeliveryType';
import { ClientMatter } from './observables/clientMatter';

@Injectable()
export class TicketService {

  usageReasons = [];
  tickets = [];
  seatTypes = [];
  ticketDeliveryTypes = [];
  clientMatters = [];
  allData = [];
  totalCount = 0;
  pageCount = 0;

  private urlGetTickets = environment.backendPath + 'getTickets.php';
  private urlGetUsageReasons = environment.backendPath + 'getAllTicketUsageReasons.php';
  private urlAddNewTicket = environment.backendPath + 'addTicket.php';
  private urlUpdateTicketStatus = environment.backendPath + 'updateTicketStatus.php';
  private urlUpdateTicket = environment.backendPath + 'updateTicket.php';
  private urlDeleteTicket = environment.backendPath + 'deleteTicket.php';
  private urlGetSeatTypes = environment.backendPath + 'getSeatTypes.php';
  private urlGetDeliveryTypes = environment.backendPath + 'getAllTicketDeliveryTypes.php';
  private urlGetAllClientMatter = environment.backendPath + 'getAllClientMatter.php';

  constructor(private http: HttpClient, private loader: LoaderService) {
    this.getUsageReasons();
    this.getSeatTypes();
    this.getTicketDeliveryTypes();
    this.getClientMatters();
  }

  getUsageReasons() {
    this.loader.show();
    this.http.get<UsageReason>(this.urlGetUsageReasons + "?tsp=" + new Date()).subscribe(
      data => this.fetchUsageReasonData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    );
  }

  private fetchUsageReasonData(data) {
    this.usageReasons = data;
    this.loader.hide();
  }

  private getTicketDeliveryTypes() {
    this.loader.show();
    this.http.get<TicketDeliveryType>(this.urlGetDeliveryTypes + "?tsp=" + new Date()).subscribe(
      data => this.fetchTicketDeliveryTypeData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    )
  }

  private fetchTicketDeliveryTypeData(data) {
    this.ticketDeliveryTypes = data;
    this.loader.hide();
  }

  private getClientMatters() {
    this.loader.show();
    this.http.get<ClientMatter>(this.urlGetAllClientMatter + "?tsp=" + new Date()).subscribe(
      data => this.fetchClientMatterData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    )
  }

  private fetchClientMatterData(data) {
    this.allData = data;
    if(data != null){
      this.totalCount = data.length;

      let count = Math.floor(data.length / 50);
      let realValue = data.length / 50;

      if(realValue > count){
        this.pageCount = count + 1;
      }
      else{
        this.pageCount = count;
      }

      this.clientMatters = this.allData.slice(0, 50);
    }

    this.loader.hide();
  }

  getFilterClientMatterData(start, end){
    this.clientMatters = this.allData.slice(start, end);
    console.log(this.clientMatters)
  }

  private getSeatTypes() {
    this.loader.show();
    this.http.get<SeatType>(this.urlGetSeatTypes + "?tsp=" + new Date()).subscribe(
      data => this.fetchSeatTypeData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    )
  }

  private fetchSeatTypeData(data) {
    this.seatTypes = data;
    this.loader.hide();
  }

  updateTicketStatus(ticketRecordNumber, newStatus) {
    let ticket = new Ticket();
    ticket.TicketRecordNumber = ticketRecordNumber;
    ticket.Status = newStatus;
    return this.http.post<any>(this.urlUpdateTicketStatus, JSON.stringify(ticket));
  }

  getTickets(eventID?: string) {
    this.loader.show();
    const options = eventID ?
    { params: new HttpParams().set(
      'EventID', eventID)
    } : {};
    this.http.get<Ticket>(this.urlGetTickets + "?tsp=" + new Date(), options).subscribe(
      data => this.fetchTicketData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    );
  }

  private fetchTicketData(data) {
    this.tickets = data;
    this.loader.hide();
  }

  addNewTicket(newTicketObj) {
    this.loader.show();
    return this.http.post(this.urlAddNewTicket, JSON.stringify(newTicketObj));
  }

  updateTicket(updatedTicketObj) {
    this.loader.show();
    return this.http.post(this.urlUpdateTicket, JSON.stringify(updatedTicketObj));
  }

  deleteTicket(ticket) {
    this.loader.show();
    return this.http.post<any>(this.urlDeleteTicket, JSON.stringify(ticket));
  }
}
