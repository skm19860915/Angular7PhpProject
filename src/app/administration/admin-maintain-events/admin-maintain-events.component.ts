import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { EventService } from '../../services/event.service';
import { Event } from '../../services/observables/event'
import { DatePipe } from '@angular/common';
import { FormControl, Validators, FormGroup } from '@angular/forms';
import { VenueService } from '../../services/venue.service';
import { NgbModalRef, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { LoaderService } from '../../services/loader/loader.service';
import { EventTemplateService } from '../../services/event-template.service';
import { TemplateEvent } from '../../services/observables/templateEvent';

@Component({
  selector: 'app-admin-maintain-events',
  templateUrl: './admin-maintain-events.component.html',
  styleUrls: ['./admin-maintain-events.component.css']
})
export class AdminMaintainEventsComponent implements OnInit {

  @ViewChild('newEventModalObj') newEventModalElement: ElementRef;
  @ViewChild('newEventTemplateModalObj') newEventTemplateModalElement: ElementRef;

  newEvent;
  editedEvent;
  newEventModal: NgbModalRef;
  editEventModal: NgbModalRef;
  pageNbr: number = 1;
  newEventTemplateModal: NgbModalRef;
  selectedTemplate;
  newEventForTemplate;
  newEvents = [];
  eventsPerPage = 5;
  eventsPageNumber = 1;
  selectedEventIndex = 1;

  constructor(
    public eventService: EventService,
    public venueService: VenueService,
    public templateService: EventTemplateService,
    private datePipe: DatePipe,
    private modalService: NgbModal,
    private loader: LoaderService
  ) { }

  ngOnInit() {
    this.init();
  }

  private init() {
    this.getEvents();
    this.eventService.getEventCategories();
    this.templateService.getEventTemplates();
  }

  openModal(modal, content) {
    switch (modal) {
      case "newEventModal":
        this.newEventModal = this.modalService.open(content, { backdrop: "static" });
        this.newEventModal.result.then(() => {
          this.newEvent = null;
        }, () => {
          this.newEvent = null;
        });
      break;
      case "editEventModal":
        this.editEventModal = this.modalService.open(content, { backdrop: "static" });
        this.editEventModal.result.then(() => {
          this.editedEvent = null;
        }, () => {
          this.editedEvent = null;
        });
      break;
      case "newEventTemplateModal":
        this.newEventTemplateModal = this.modalService.open(content, { size: 'lg', backdrop: "static" });
        this.newEventTemplateModal.result.then(() => {
          this.discardEventTemplate();
        }, () => {
          this.discardEventTemplate();
        });
      break;
    }
  }

  private getEvents() {
    this.eventService.getEventsWithVenueInfo();
    this.venueService.getAllEventVenues();
  }

  resetEditedEvent(): void {
    this.editedEvent = null;
    this.eventService.getEventsWithVenueInfo();
  }

  private getIndexOfVenue(venueNumber): number {
    for (let i = 0; i < this.venueService.venues.length; i++) {
      if (this.venueService.venues[i].VenueNumber == venueNumber)
        return i;
    }
    return -1;
  }

  updateEvent() {
    if (this.eventValid(this.editedEvent)) {
      // Create custom obj to get rid of form controls
      let updatedEventObjLight = {
        'EventNumber': this.editedEvent.EventNumber,
        'EventDescription': this.editedEvent.formctrl_eventTitle.value,
        'updatedFormattedEventDateTime': this.datePipe.transform(this.editedEvent.formgrp_eventDateTime.controls.formctrl_eventDateTime.value, 'yyyy-MM-dd HH:mm:ss'),
        'AssociatedTicketCategory': this.editedEvent.AssociatedTicketCategory,
        'AssociatedVenueNumber': this.venueService.venues[this.editedEvent.SelectedVenueIndex].VenueNumber,
        'Status': this.editedEvent.Status
      };

      this.eventService.updateEvent(updatedEventObjLight).subscribe(
        () => {
          this.resetEditedEvent();
          this.editEventModal.close();
          this.eventService.getEventsWithVenueInfo();
          swal("Success!", "The event was successfully updated.", "success");
          this.loader.hide();
        },
        err => {
          swal("Error!", "An error occured while updating the event.", "error");
          console.error(err);
          this.loader.hide();
        }
      )
    }
    else
      swal("Error!", "Event details are invalid.", "error");
  }

  editEvent(eventNbr) {
    // Save copying
    this.editedEvent = JSON.parse(JSON.stringify(this.eventService.events[this.eventService.events.findIndex(e => e.EventNumber === eventNbr)]));

    this.editedEvent.SelectedVenueIndex = this.getIndexOfVenue(this.editedEvent.AssociatedVenueNumber);

    if (!this.editedEvent.formctrl_eventTitle) {
      this.editedEvent.formctrl_eventTitle = new FormControl('', Validators.required);
    }

    this.editedEvent.formctrl_eventTitle.setValue(this.editedEvent.EventDescription);

    if (!this.editedEvent.formgrp_eventDateTime) {
      this.editedEvent.formgrp_eventDateTime = new FormGroup({
        formctrl_eventDateTime: new FormControl('', Validators.required)
      });
    }

    const eventDate = new Date(
      this.datePipe.transform(
        this.editedEvent.EventDateTime.date.replace(" ", "T").split(".")[0], 'M/d/y h:mm a'
      )
    );

    this.editedEvent.formgrp_eventDateTime.controls.formctrl_eventDateTime.setValue(
      eventDate
    );
  }

  private buildModal(event) {
    let modaltext = event.EventNumber;
    modaltext += " | ";
    modaltext += event.AssociatedTicketCategory;
    modaltext += " | ";
    modaltext += event.EventDescription;
    modaltext += " | ";
    modaltext += this.datePipe.transform(
      event.EventDateTime.date.replace(" ", "T").split(".")[0], 'EEEE, M/d/y @ h:mm a'
    );
    modaltext += " | ";
    modaltext += event.Name;
    return modaltext;
  }

  deleteEvent(eventNbr) {
    const i = this.eventService.events.findIndex(e => e.EventNumber === eventNbr);
    const event = this.eventService.events[i];
    swal({
      title: "You are about to delete the following event:",
      text: this.buildModal(event),
      icon: "warning",
      buttons: [true, true],
      dangerMode: true
      })
      .then((willApprove) => {
        if (willApprove) {
          this.eventService.deleteEvent(i).subscribe(
            () => {
              this.eventService.getEventsWithVenueInfo();
              swal("Success!", "The event was successfully deleted.", "success");
              this.loader.hide();
            },
            err => {
              swal("Error!", "An error occured while deleting the event.", "error");
              console.error(err);
              this.loader.hide();
            }
          );
        }
      });
  }

  addEvent() {
    if (this.newEvent.SelectedVenueIndex > -1)
      this.newEvent.AssociatedVenueNumber = this.venueService.venues[this.newEvent.SelectedVenueIndex].VenueNumber;

    if (this.newEvent.formgrp_eventDateTime.controls.formctrl_eventDateTime)
      this.newEvent.formattedEventDateTime =
        this.datePipe.transform(this.newEvent.formgrp_eventDateTime.controls.formctrl_eventDateTime.value, 'yyyy-MM-dd HH:mm:ss');

    if (this.eventValid(this.newEvent)) {
      // Create custom obj to get rid of form controls
      let newEventWithOutFromCtrl = {
        'EventDescription': this.newEvent.formctrl_eventTitle.value,
        'formattedEventDateTime': this.newEvent.formattedEventDateTime,
        'AssociatedTicketCategory': this.newEvent.AssociatedTicketCategory,
        'AssociatedVenueNumber': this.newEvent.AssociatedVenueNumber,
        'Status': this.newEvent.Status
      };
      this.eventService.addEvent(newEventWithOutFromCtrl).subscribe(
        () => {
          this.eventService.getEventsWithVenueInfo();
          this.newEvent = null;
          this.newEventModal.close();
          swal("Success!", "The event was successfully saved.", "success");
          this.loader.hide();
        },
        err => {
          swal("Error!", "An error occured while saving the event.", "error");
          console.error(err);
          this.loader.hide();
        }
      );
    }
    else
      swal("Error!", "Event details are invalid.", "error");
  }

  preFillTitleFromCategory(category) {
    if (!this.newEvent.EventDescription || this.newEvent.EventDescription.length == 0) {
      let preFillValue = '';
      switch (category) {
        case "CAVS":
          preFillValue = 'Cavs Vs. ';
          break;
        case "BROWNS":
          preFillValue = 'Browns Vs. ';
          break;
        case "MONSTERS":
          preFillValue = 'Monsters Vs. ';
          break;
        case "INDIANS":
          preFillValue = 'Indians Vs. ';
          break;
      }
      this.newEvent.EventDescription = preFillValue;
    }
    else {
      if (this.newEvent.EventDescription == 'Cavs Vs. '
        || this.newEvent.EventDescription == 'Browns Vs. '
        || this.newEvent.EventDescription == 'Monsters Vs. '
        || this.newEvent.EventDescription == 'Indians Vs. ') {
        this.newEvent.EventDescription = null;
        this.preFillTitleFromCategory(category);
      }
    }
  }

  private eventValid(eventObj): Boolean {
    if (!eventObj.AssociatedTicketCategory)
      return false;
    if (!eventObj.AssociatedVenueNumber)
      return false;
    if (!eventObj.formctrl_eventTitle.valid)
      return false;
    if (!eventObj.formgrp_eventDateTime.controls.formctrl_eventDateTime.valid)
      return false;
    return true;
  }

  public addEventOrEventTemplate(){
    swal({
      title: 'Do you want to use a event template?',
      buttons: {
        no: {
          text: 'No',
          visible: true,
          className: 'swal-btn-no'
        },
        yes: {
          text: 'Yes',
          visible: true,
          className: 'swal-btn-yes',
          value: 'yes'
        }
      },
      className: 'swal-centered-buttons'
    })
    .then((value) => {
      if (value === 'yes') {
        this.initTemplateModal();
        this.openModal('newEventTemplateModal', this.newEventTemplateModalElement);
      }
      else{
        this.initNewModal();
        this.openModal('newEventModal', this.newEventModalElement);
      }
    });
  }

  initNewModal() {
    this.newEvent = new Event();
    this.newEvent.SelectedVenueIndex = -1;
    this.newEvent.Status = 'AVAILABLE';
    this.newEvent.formctrl_eventTitle = new FormControl('', Validators.required);
    this.newEvent.formgrp_eventDateTime = new FormGroup({formctrl_eventDateTime: new FormControl('', Validators.required)});
  }

  initTemplateModal(){
    this.newEventForTemplate = new TemplateEvent();
    this.newEventForTemplate.Status = 'AVAILABLE';
    this.newEventForTemplate.RelativeVenueNumber = -1;
    this.newEventForTemplate.VenueName = null;
    this.newEventForTemplate.formctrl_TitleDescription = new FormControl('', Validators.required);
    this.newEventForTemplate.formgrp_DateTime = new FormGroup({formctrl_DateTime: new FormControl('', Validators.required)});
  }

  loadSelectTemplateInformation(t){
    //console.log(t);
    this.newEvents = [];
    this.selectedEventIndex = 1;

    t.events.forEach((event, index) => {
      this.insertTemplateEventsToNewEvents(event, index);
    });
    this.newEventForTemplate = this.newEvents[0];
  }

  insertTemplateEventsToNewEvents(item, i) {
    var e = new TemplateEvent();
    e.EventNumber = item.EventNumber;
    e.Status = (item.Status === 1) ? "AVAILABLE" : "NOT AVAILABLE";
    e.RelativeCategory = item.RelativeCategory;
    e.RelativeVenueNumber = item.RelativeVenueNumber === null ? -1 : item.RelativeVenueNumber;
    let venue = this.venueService.venues.find(x => x.VenueNumber === item.RelativeVenueNumber);
    e['VenueName'] = venue ? venue.Name : null;
    e['formctrl_TitleDescription'] = new FormControl('', Validators.required);
    e['formctrl_TitleDescription'].setValue(item.TitleDescription);
    e.DateAndTime = item.DateAndTime.date.toString();
    e['calendar_DateAndTime'] = new Date(e.DateAndTime);
    this.newEvents.push(e);
  }

  reloadSelectedEventData(e){
    this.newEventForTemplate = e;
  }

  discardEventTemplate(){
    if (this.newEvents.length > 0) {
      this.newEvents = [];
      this.selectedTemplate = null;
      this.newEventForTemplate = null;
      this.eventsPageNumber = 1;
      this.selectedEventIndex = 1;
    }
  }

  addEventsOfTemplate(){
    if (this.modalEventsValid(this.newEvents)) {
      //console.log(this.newEvents);
      for (let i = 0; i < this.newEvents.length; i++) {

        this.newEvents[i].TitleDescription = this.newEvents[i].formctrl_TitleDescription.value;
        this.newEvents[i].DateAndTime = this.datePipe.transform(this.newEvents[i].calendar_DateAndTime, 'yyyy-MM-dd HH:mm:ss');

        this.eventService.addNewEvents(this.newEvents[i]).subscribe(
          () => {
            this.eventService.getEventsWithVenueInfo();
            this.newEventTemplateModal.close();
            swal("Success!", "The event was successfully saved.", "success");
            this.loader.hide();
          },
          err => {
            if (i === this.newEvents.length - 1) { // LAST
              swal('Error!', 'An error occured while adding the new ticket(s)', 'error');
              console.error(err);
            }
            this.loader.hide();
          }
        );
      }
    } else {
      swal('Error!', 'Ticket details are invalid.', 'error');
    }
  }

  modalEventsValid(events): boolean {
    let valid = true;

    if(!this.newEventForTemplate){
      valid = false;
    }

    for (let i = 0; i < events.length; i++) {
      if (!events[i].EventNumber || events[i].EventNumber == 0) {
        valid = false;
      }
      if (!events[i].RelativeCategory) {
        valid = false;
      }
      if (events[i].RelativeVenueNumber === -1) {
        valid = false;
      }
      if (!events[i].formctrl_TitleDescription.valid) {
        valid = false;
      }
    }
    return valid;
  }
}
