import { Component, OnInit, ElementRef, ViewChild, ViewContainerRef } from '@angular/core';
import { NgbModalRef, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { EventService } from '../../services/event.service';
import { FormControl, Validators } from '@angular/forms';
import swal from 'sweetalert';
import { LoaderService } from '../../services/loader/loader.service';
import { VenueService } from '../../services/venue.service';
import { TemplateEvent } from '../../services/observables/templateEvent';
import { EventTemplateService } from '../../services/event-template.service';

@Component({
  selector: 'app-admin-maintain-event-templates',
  templateUrl: './admin-maintain-event-templates.component.html',
  styleUrls: ['./admin-maintain-event-templates.component.css']
})

export class AdminMaintainEventTemplatesComponent implements OnInit {

 @ViewChild('editTemplateModalObj') editModal: ElementRef;

  newTemplateModal: NgbModalRef;
  editTemplateModal: NgbModalRef;
  modalTemplate = {};
  modalTemplateNameFormCtrl;
  pageNbr = 1;
  templateEventsPageNbr = 1;
  maxPages = 10;
  maxTemplateEventsPages = 10;

  constructor(
    private eventService: EventService,
    private venueService: VenueService,
    private modalService: NgbModal,
    private loader: LoaderService,
    public eventTemplateService: EventTemplateService
  ) { }

  ngOnInit() {
    this.eventTemplateService.getEventTemplates();
    this.venueService.getAllEventVenues();
    this.eventService.getEventCategories();
  }

  openModal(modal, content) {
    switch (modal) {
      case 'newTemplateModal':
        this.initTemplate();
        this.newTemplateModal = this.modalService.open(content, { size: 'lg', backdrop: 'static' });
        this.newTemplateModal.result.then(() => {
          this.discardTemplate();
        }, () => {
          this.discardTemplate();
        });
        break;
      case 'editEventModal':
        this.editTemplateModal = this.modalService.open(content, { size: 'lg', backdrop: 'static' });
        this.editTemplateModal.result.then(() => {
          this.discardTemplate();
        }, () => {
          this.discardTemplate();
        });
        break;
    }
  }

  private getAbsoluteIndex(pageNbr, itemsPerPage, i) {
    return pageNbr * itemsPerPage - itemsPerPage + i;
  }

  private removeEvent(i) {
    this.modalTemplate['events'].splice(i, 1);
  }

  private getVenueName (number){
    let selectVenue = this.venueService.venues.find(x => x.VenueNumber === number);
    if(selectVenue){
      return selectVenue.Name
    }
  }

  private discardTemplate() {
    this.modalTemplate = {};
    this.modalTemplateNameFormCtrl = null;
  }

  private addEventToTemplate() {
    this.modalTemplate['events'].push(new TemplateEvent());
  }

  private initTemplate() {
    this.modalTemplate['events'] = [
      new TemplateEvent()
    ];
    this.modalTemplateNameFormCtrl = new FormControl('', Validators.required);
  }

  private addTemplate() {
    if (this.newTemplateFormValid()) {
      this.modalTemplate['EventTemplateName'] = this.modalTemplateNameFormCtrl.value;
      this.eventTemplateService.addEventTemplate(this.modalTemplate).subscribe(
        () => {
          this.eventTemplateService.getEventTemplates();
          swal('Success!', 'The event template was successfully created.', 'success');
          this.newTemplateModal.close();
          this.loader.hide();
        },
        err => {
          swal('Error!', 'An error occured while submitting the event template.', 'error');
          console.log(err);
          this.loader.hide();
        }
      );
    } else {
      swal('Error!', 'Template details are invalid.', 'error');
    }
  }

  private newTemplateFormValid() {
    if (!this.modalTemplateNameFormCtrl.valid) {
      return false;
    }
    return true;
  }
}
