import { Component, OnInit, ElementRef, ViewChild, ViewContainerRef } from '@angular/core';
import { TicketTemplateService } from '../../services/ticket-template.service';
import { NgbModalRef, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { EventService } from '../../services/event.service';
import { TemplateTicket } from '../../services/observables/templateTicket';
import { TicketService } from '../../services/ticket.service';
import { UserService } from '../../services/user.service';
import { FormControl, Validators } from '@angular/forms';
import swal from 'sweetalert';
import { LoaderService } from '../../services/loader/loader.service';
import { VenueService } from '../../services/venue.service';

@Component({
  selector: 'app-admin-maintain-ticket-templates',
  templateUrl: './admin-maintain-ticket-templates.component.html',
  styleUrls: ['./admin-maintain-ticket-templates.component.css']
})
export class AdminMaintainTicketTemplatesComponent implements OnInit {

  @ViewChild('editTemplateModalObj') editModal: ElementRef;

  newTemplateModal: NgbModalRef;
  editTemplateModal: NgbModalRef;
  modalTemplate = {};
  modalTemplateNameFormCtrl;
  selVenue;
  preFillActive = true;
  pageNbr = 1;
  templateTicketsPageNbr = 1;
  maxPages = 10;
  maxTemplateTicketsPages = 10;

  constructor(
    public ticketTemplateService: TicketTemplateService,
    private eventService: EventService,
    private venueService: VenueService,
    private modalService: NgbModal,
    private loader: LoaderService,
    private userService: UserService,
    private ticketService: TicketService
  ) { }

  ngOnInit() {
    this.ticketTemplateService.getTicketTemplates();
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

  private editTemplate(i) {
    this.modalTemplate = this.ticketTemplateService.ticketTemplates[i];
    this.selVenue = this.venueService.venues[
      this.venueService.venues.findIndex(
        v => v.VenueNumber === this.ticketTemplateService.ticketTemplates[i].AssociatedVenueNumber
      )
    ];
    this.modalTemplateNameFormCtrl = new FormControl('', Validators.required);
    this.modalTemplateNameFormCtrl.setValue(this.ticketTemplateService.ticketTemplates[i].TicketTemplateName);
    this.openModal('editEventModal', this.editModal);
  }

  private deleteTicketTemplate(i): void {

    swal({
      title: 'You\'re about to delete the following ticket template:',
      text: this.ticketTemplateService.ticketTemplates[i].TicketTemplateName,
      icon: 'warning',
      buttons: [true, true],
      dangerMode: true
    })
      .then((willCancel) => {
        if (willCancel) {
          this.ticketTemplateService.deleteTicketTemplate(i).subscribe(
            () => {
              this.ticketTemplateService.getTicketTemplates();
              swal('Success!', 'Ticket template was successfully deleted.', 'success');
              this.loader.hide();
            },
            err => {
              swal('Error!', 'An error occured while deleting the ticket template.', 'error');
              console.error(err);
              this.loader.hide();
            }
          );
        }
      });
  }

  private removeTicket(i) {
    this.modalTemplate['tickets'].splice(i, 1);
  }

  private discardTemplate() {
    this.modalTemplate = {};
    this.selVenue = null;
    this.modalTemplateNameFormCtrl = null;
  }

  private addTicketToTemplate() {
    this.modalTemplate['tickets'].push(new TemplateTicket());
  }

  private selectVenueForTemplate(venue) {
    this.selVenue = venue;
    this.modalTemplate['AssociatedVenueNumber'] = venue.VenueNumber;
  }

  private selectCategoryForTemplate(category) {
    this.modalTemplate['AssociatedTicketCategory'] = category;
  }

  private initTemplate() {
    this.modalTemplate['tickets'] = [
      new TemplateTicket()
    ];
    this.modalTemplateNameFormCtrl = new FormControl('', Validators.required);
  }

  private addTemplate() {
    if (this.newTemplateFormValid()) {
      // Copy form ctrl values to template obj
      this.modalTemplate['TicketTemplateName'] = this.modalTemplateNameFormCtrl.value;
      this.ticketTemplateService.addTicketTemplate(this.modalTemplate).subscribe(
        () => {
          this.ticketTemplateService.getTicketTemplates();
          swal('Success!', 'The ticket template was successfully created.', 'success');
          this.newTemplateModal.close();
          this.loader.hide();
        },
        err => {
          swal('Error!', 'An error occured while submitting the ticket template.', 'error');
          console.log(err);
          this.loader.hide();
        }
      );
    } else {
      swal('Error!', 'Template details are invalid.', 'error');
    }
  }

  private updateTemplate() {
    if (this.newTemplateFormValid()) {
      // Copy form ctrl values to template obj
      this.modalTemplate['NewTicketTemplateName'] = this.modalTemplateNameFormCtrl.value;
      this.ticketTemplateService.updateTicketTemplate(this.modalTemplate).subscribe(
        () => {
          this.ticketTemplateService.getTicketTemplates();
          swal('Success!', 'The ticket template was successfully edited.', 'success');
          this.editTemplateModal.close();
          this.loader.hide();
        },
        err => {
          swal('Error!', 'An error occured while editing the ticket template.', 'error');
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

  private ticketDetailInputChange(input: string, index: number, field: string) {
    if (this.preFillActive && index == 0) {
      for (let i = 1; i < this.modalTemplate['tickets'].length; i++) {
        switch (field) {
          case 'AvailableTo':
            this.modalTemplate['tickets'][i].AvailableTo = this.modalTemplate['tickets'][0].AvailableTo;
            break;
          case 'AutoApprove':
            this.modalTemplate['tickets'][i].AutoApprove = this.modalTemplate['tickets'][0].AutoApprove;
            break;
          case 'AssociatedSeatType':
            this.modalTemplate['tickets'][i].AssociatedSeatType = this.modalTemplate['tickets'][0].AssociatedSeatType;
            break;
          case 'AssociatedDeliveryType':
            this.modalTemplate['tickets'][i].AssociatedDeliveryType = this.modalTemplate['tickets'][0].AssociatedDeliveryType;
            break;
          case 'Section':
            this.modalTemplate['tickets'][i].Section = this.modalTemplate['tickets'][0].Section;
            break;
          case 'Row':
            this.modalTemplate['tickets'][i].Row = this.modalTemplate['tickets'][0].Row;
            break;
        }
      }
    }
  }
}
