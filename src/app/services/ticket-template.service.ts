import { Injectable } from '@angular/core';
import { environment } from '../../environments/environment';
import { HttpClient } from '@angular/common/http';
import { LoaderService } from './loader/loader.service';
import { TemplateTicket } from './observables/templateTicket';

@Injectable({
  providedIn: 'root'
})
export class TicketTemplateService {

  private getTemplatesURL = environment.backendPath + 'getAllTicketTemplates.php';
  private addTemplateURL = environment.backendPath + 'addTicketTemplate.php';
  private updateTemplateURL = environment.backendPath + 'updateTicketTemplate.php';
  private deleteTemplateURL = environment.backendPath + 'deleteTicketTemplate.php';

  ticketTemplates = [];

  constructor(private http: HttpClient, private loader: LoaderService) { }

  getTicketTemplates() {
    this.loader.show();
    this.http.get<any>(this.getTemplatesURL + "?tsp=" + new Date()).subscribe(
      data => this.fetchTemplateData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    );
  }

  addTicketTemplate(ticketTemplate) {
    //console.log("post....", ticketTemplate);
    this.loader.show();
    return this.http.post(this.addTemplateURL, JSON.stringify(ticketTemplate));
  }

  deleteTicketTemplate(i) {
    this.loader.show();
    const jsonToSend = {
      'TicketTemplateName': this.ticketTemplates[i].TicketTemplateName
    }
    return this.http.post(this.deleteTemplateURL, JSON.stringify(jsonToSend));
  }

  updateTicketTemplate(ticketTemplate) {
    this.loader.show();
    return this.http.post(this.updateTemplateURL, JSON.stringify(ticketTemplate));
  }

  private fetchTemplateData(data) {

    // Clear arrays
    if (this.ticketTemplates.length > 0) {
      for (let i = 0; i < this.ticketTemplates.length; i++) {
        this.ticketTemplates[i]["tickets"] = [];
      }
      this.ticketTemplates = [];
    }

    for (let i = 0; i < data.length; i++) {

      let templateTicket = new TemplateTicket();
      templateTicket.TicketNumber = data[i]["TicketNumber"];
      templateTicket.AvailableTo = data[i]["AvailableTo"];
      templateTicket.AssociatedDeliveryType = data[i]["AssociatedDeliveryType"];
      templateTicket.AutoApprove = data[i]["AutoApprove"];
      templateTicket.AssociatedSeatType = data[i]["AssociatedSeatType"];
      templateTicket.Section = data[i]["Section"];
      templateTicket.Row = data[i]["Row"];
      templateTicket.Seat = data[i]["Seat"];

      let idx = this.ticketTemplates.findIndex(t => t.TicketTemplateName === data[i].TicketTemplateName);

      if (idx !== -1) {
        this.ticketTemplates[idx]["tickets"].push(templateTicket);
      }
      else {
        const templateObj = {
          TicketTemplateName: data[i].TicketTemplateName,
          AssociatedVenueNumber: data[i].AssociatedVenueNumber,
          AssociatedTicketCategory: data[i].AssociatedTicketCategory,
          tickets: [templateTicket]
        }
        if (this.ticketTemplates.length > 0) {
          this.ticketTemplates.push(templateObj);
        }
        else {
          this.ticketTemplates = [templateObj];
        }
      }
    }
    this.loader.hide();
  }
}
