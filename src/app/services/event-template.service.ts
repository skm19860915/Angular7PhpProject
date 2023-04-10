import { Injectable } from '@angular/core';
import { environment } from '../../environments/environment';
import { HttpClient } from '@angular/common/http';
import { LoaderService } from './loader/loader.service';
import { TemplateEvent } from './observables/templateEvent';

@Injectable({
  providedIn: 'root'
})
export class EventTemplateService {

  private getTemplatesURL = environment.backendPath + 'getAllEventTemplates.php';
  private addTemplateURL = environment.backendPath + 'addEventTemplate.php';

  eventTemplates = [];

  constructor(private http: HttpClient, private loader: LoaderService) { }

  getEventTemplates() {
    this.loader.show();
    this.http.get<any>(this.getTemplatesURL + "?tsp=" + new Date()).subscribe(
      data => this.fetchTemplateData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    );
  }

  addEventTemplate(eventTemplate) {
    //console.log("post....", eventTemplate);
    this.loader.show();
    return this.http.post(this.addTemplateURL, JSON.stringify(eventTemplate));
  }

  private fetchTemplateData(data) {

    // Clear arrays
    if (this.eventTemplates.length > 0) {
      for (let i = 0; i < this.eventTemplates.length; i++) {
        this.eventTemplates[i]["events"] = [];
      }
      this.eventTemplates = [];
    }

    for (let i = 0; i < data.length; i++) {

      let templateEvent = new TemplateEvent();
      templateEvent.EventNumber = data[i]["EventNumber"];
      templateEvent.Status = data[i]["Status"];
      templateEvent.RelativeCategory = data[i]["RelativeCategory"];
      templateEvent.RelativeVenueNumber = data[i]["RelativeVenueNumber"];
      templateEvent.TitleDescription = data[i]["TitleDescription"];
      templateEvent.DateAndTime = data[i]["DateAndTime"];

      let idx = this.eventTemplates.findIndex(t => t.EventTemplateName === data[i].EventTemplateName);

      if (idx !== -1) {
        this.eventTemplates[idx]["events"].push(templateEvent);
      }
      else {
        const templateObj = {
          EventTemplateName: data[i].EventTemplateName,
          events: [templateEvent]
        }
        if (this.eventTemplates.length > 0) {
          this.eventTemplates.push(templateObj);
        }
        else {
          this.eventTemplates = [templateObj];
        }
      }
    }
    this.loader.hide();
  }
}
