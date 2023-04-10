import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { LoaderService } from './loader/loader.service';

@Injectable({
  providedIn: 'root'
})
export class SpecialRequestService {

  public statusList = [
    'APPROVED',
    'SUBMITTED',
    'NOT APPROVED',
    'CANCELLED'
  ];

  private getSpecialTicketRequestsURL = environment.backendPath + 'getAllSpecialTicketRequests.php';
  private addSpecialTicketRequestsURL = environment.backendPath + 'addSpecialTicketRequest.php';
  private addRequestsReplyURL = environment.backendPath + 'addSpecialTicketRequestReply.php';
  private updateSpecialTicketRequestsURL = environment.backendPath + 'updateSpecialTicketRequest.php';

  specialTicketRequests = [];

  constructor(private http: HttpClient, private loader: LoaderService) { }

  getSpecialTicketRequests(): void {
    this.loader.show();
    this.http.get<Request>(this.getSpecialTicketRequestsURL + "?tsp=" + new Date()).subscribe(
      data => this.fetchTicketRequestData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    );
  }

  addNewSpecialRequest(specialRequest) {
    this.loader.show();
    return this.http.post(this.addSpecialTicketRequestsURL, JSON.stringify(specialRequest));
  }

  addNewRequestReply(requestReply) {
    this.loader.show();
    return this.http.post(this.addRequestsReplyURL, JSON.stringify(requestReply));
  }

  approveSpecialTicketRequest(i) {
    this.loader.show();
    let jsonInput = {
      SpecialRequestNumber: this.specialTicketRequests[i].SpecialRequestNumber,
      Status: this.statusList[0]
    }
    return this.http.post(this.updateSpecialTicketRequestsURL, JSON.stringify(jsonInput));
  }

  denySpecialTicketRequest(i) {
    this.loader.show();
    let jsonInput = {
      SpecialRequestNumber: this.specialTicketRequests[i].SpecialRequestNumber,
      Status: this.statusList[2]
    }
    return this.http.post(this.updateSpecialTicketRequestsURL, JSON.stringify(jsonInput));
  }

  cancelSpecialTicketRequest(i) {
    this.loader.show();
    let jsonInput = {
      SpecialRequestNumber: this.specialTicketRequests[i].SpecialRequestNumber,
      Status: this.statusList[3]
    }
    return this.http.post(this.updateSpecialTicketRequestsURL, JSON.stringify(jsonInput));
  }

  private fetchTicketRequestData(data): void {
    this.specialTicketRequests = data;
    this.loader.hide();
  }
}