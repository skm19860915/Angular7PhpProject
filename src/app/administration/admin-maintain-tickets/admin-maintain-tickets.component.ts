import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { TicketService } from '../../services/ticket.service';
import { EventService } from '../../services/event.service';
import { Ticket } from '../../services/observables/ticket';
import { FormControl, Validators } from '@angular/forms';
import { NgbModalRef, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { LoaderService } from '../../services/loader/loader.service';
import { DatePipe } from '@angular/common';
import { Category } from '../../services/observables/category';
import { TicketTemplateService } from '../../services/ticket-template.service';
import { VenueService } from '../../services/venue.service';
import { tick } from '@angular/core/src/render3';
declare var $: any;

@Component({
  selector: 'app-admin-maintain-tickets',
  templateUrl: './admin-maintain-tickets.component.html',
  styleUrls: ['./admin-maintain-tickets.component.css']
})
export class AdminMaintainTicketsComponent implements OnInit {

  @ViewChild('newTicketsModalObj') newTicketsModalElement: ElementRef;

  selectedTickets = [];
  editedTickets = [];
  newTickets = [];
  prefillTemplate;
  filterEvent;
  filterCategory;
  selEvent;
  selEventCategory;
  selEventDescription;
  selEventDateTime;
  newTicketIndex = 0;
  selTicketIndex = 1;
  preFillActive = false;
  ticketTemplateUse = false;
  selectForDeletionOnly = false;
  newTicketsModal: NgbModalRef;
  editTicketsModal: NgbModalRef;
  pageNbr = 1;
  actionSelectAll = false;
  newTicketsItemsPerPage = 5;
  newTicketsPageNbr = 1;
  editedTicketsItemsPerPage = 5;
  editedTicketsPageNbr = 1;

  newTicket;

  constructor(
    public ticketService: TicketService,
    public eventService: EventService,
    public venueService: VenueService,
    public templateService: TicketTemplateService,
    private modalService: NgbModal,
    private loader: LoaderService,
    private datePipe: DatePipe
  ) { }

  ngOnInit() {
    this.init();
  }

  openModal(modal, content) {
    switch (modal) {
      case 'newTicketsModal':
        this.newTicketsModal = this.modalService.open(content, { size: 'lg', backdrop: 'static' });
        this.newTicketsModal.result.then(() => {
          this.discardNewTickets();
        }, () => {
          this.discardNewTickets();
        });
        break;
      case 'editTicketsModal':
        if (!this.selectForDeletionOnly) {
          this.editTicketsModal = this.modalService.open(content, { size: 'lg', backdrop: 'static' });
          this.editTicketsModal.result.then(() => {
            this.discardSelectedTickets();
          }, () => {
            this.discardSelectedTickets();
          });
        }
        break;
    }
  }



  private prefillAddTicketForm() {
    //console.log(this.templateService.ticketTemplates, this.prefillTemplate);
    this.selEventCategory = this.eventService.eventCategories[this.eventService.eventCategories.findIndex(
      c => c.Category === this.prefillTemplate.AssociatedTicketCategory)];
  }

  private init() {
    this.eventService.getEventsWithVenueInfo();
    this.eventService.getEventCategories();
    this.venueService.getAllEventVenues();
    this.ticketService.getTickets();
    this.templateService.getTicketTemplates();
  }

  public categoryFilterUpdate(filterVal, eventFilterObj) {

    this.filterCategory = filterVal;

    if (this.filterCategory && eventFilterObj.filterVal) {
      const event = this.eventService.events[this.eventService.events.findIndex(
        e => e.EventNumber === eventFilterObj.filterVal.EventNumber
      )];

      // Clear event filter if selected event doesn't match lately selected category
      if (this.filterCategory !== event.AssociatedTicketCategory) {
        eventFilterObj.filterVal = undefined;
      }
    }
  }

  public eventFilterUpdate(filterVal, categoryFilterObj) {

    this.filterEvent = filterVal;

    if (this.filterEvent) {
      const event = this.eventService.events[this.eventService.events.findIndex(
        e => e.EventNumber === this.filterEvent.EventNumber
      )];
      this.filterCategory = event.AssociatedTicketCategory;

      // Set category filter automatically
      this.filterCategory = event.AssociatedTicketCategory;
      categoryFilterObj.filterVal = event.AssociatedTicketCategory;
    }
  }

  selectEventForTicket(event) {
    this.selEvent = event;
    this.selEventCategory = this.eventService.eventCategories[this.eventService.eventCategories.findIndex(
      e => e.Category === event.AssociatedTicketCategory)];
    this.setEventForTickets(event);
  }

  private setEventForTickets(event) {
    if (this.newTickets.length > 0) {
      for (let i = 0; i < this.newTickets.length; i++) {
        this.newTickets[i].AssociatedEventNumber = event.EventNumber;
        this.newTickets[i].AssociatedTicketCategory = event.AssociatedTicketCategory;
        this.newTickets[i].AssociatedVenueNumber = event.AssociatedVenueNumber;
      }
    }
    if (this.selectedTickets.length > 0) {
      for (let i = 0; i < this.selectedTickets.length; i++) {
        this.selectedTickets[i].AssociatedEventNumber = event.EventNumber;
        this.selectedTickets[i].AssociatedTicketCategory = event.AssociatedTicketCategory;
        this.selectedTickets[i].AssociatedVenueNumber = event.AssociatedVenueNumber;
      }
    }
  }

  setEventCategory(category) {
    this.selEventCategory = category;
    for (let i = 0; i < this.newTickets.length; i++) {
      this.newTickets[i].AssociatedTicketCategory = category.Category;
    }
  }

  discardNewTickets() {
    if (this.newTickets.length > 0) {
      this.newTickets = [];
      this.newTicketIndex = 0;
      this.selTicketIndex = 1;
      this.selEvent = null;
      this.selEventCategory = null;
      this.selEventDescription = null;
      this.selEventDateTime = null;
      this.newTicketsPageNbr = 1;
    }
    this.ticketTemplateUse = false;
  }

  cleanUpSelectedTickets() {
    this.selectedTickets = [];
    this.newTicketIndex = 0;
    this.selTicketIndex = 1;
    this.ticketService.tickets.forEach(ticket => {
      ticket.selected = 0;
    });
    this.selectForDeletionOnly = false;
  }

  discardSelectedTickets() {
    this.selectedTickets = [];
    this.editedTickets = [];
    this.newTicketIndex = 0;
    this.selTicketIndex = 1;
    this.selEvent = null;
    this.selEventDescription = null;
    this.selEventDateTime = null;
    this.selEventCategory = null;
    this.ticketService.tickets.forEach(ticket => {
      ticket.selected = 0;
    });
    this.selectForDeletionOnly = false;
    this.actionSelectAll = false;
    this.editedTicketsPageNbr = 1;
  }

  editTickets() {
    //console.log(this.editedTickets)
    if (this.modalTicketsValid(this.editedTickets)) {
      for (let i = 0; i < this.editedTickets.length; i++) {

        // Copy form ctrl values to ticket objects
        this.editedTickets[i].Section = this.editedTickets[i].formctrl_Section.value;
        this.editedTickets[i].Row = this.editedTickets[i].formctrl_Row.value;
        this.editedTickets[i].Seat = this.editedTickets[i].formctrl_Seat.value;

        // Get event data
        this.editedTickets[i].AssociatedEventNumber = this.selEvent.EventNumber;
        this.editedTickets[i].AssociatedTicketCategory = this.selEventCategory.Category;

        this.ticketService.updateTicket(this.editedTickets[i]).subscribe(
          () => {
            if (i == this.editedTickets.length - 1) { // LAST
              this.init();
              this.editTicketsModal.close();
              swal('Success!', 'Ticket(s) successfully edited.', 'success');
            }
            this.loader.hide();
          },
          err => {
            if (i == this.editedTickets.length - 1) { // LAST
              swal('Error!', 'An error occured while editing the ticket(s)', 'error');
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

  deleteTickets() {
    let modalText = '';
    modalText = this.buildModalList();

    if (this.selectedTickets.length > 0) {
      swal({
        title: 'You are about to delete the following tickets:',
        text: modalText,
        icon: 'warning',
        buttons: [true, true],
        dangerMode: true
      })
        .then((willApprove) => {
          if (willApprove) {
            for (let i = 0; i < this.selectedTickets.length; i++) {
              this.ticketService.deleteTicket(this.selectedTickets[i]).subscribe(
                () => {
                  if (i == this.selectedTickets.length - 1) { //LAST
                    this.discardSelectedTickets();
                    this.ticketService.getTickets();
                    swal('Success!', 'Ticket(s) successfully deleted.', 'success');
                  }
                  this.loader.hide();
                },
                err => {
                  if (i == this.selectedTickets.length - 1) { //LAST
                    swal('Error!', 'An error occured while deleting the ticket(s)', 'error');
                    console.error(err);
                  }
                  this.loader.hide();
                }
              );
            }
          } else {
            this.discardSelectedTickets();
          }
        });
    }
  }

  private buildModalList(): string {
    let modalText = '';
    for (let i = 0; i < this.selectedTickets.length; i++) {
      const ticket = this.selectedTickets[i];
      const event = this.eventService.events[this.eventService.events.findIndex(
        e => e.EventNumber === ticket.AssociatedEventNumber
      )];
      if (event && event.EventDescription) {
        modalText += event.EventDescription;
        modalText += ' | ';
        modalText += this.datePipe.transform(
          event.EventDateTime.date.replace(' ', 'T').split('.')[0], 'EEEE, M/d/y @ h:mm a'
        );
      } else {
        modalText += 'EVENT NOT FOUND';
      }
      modalText += ' | ';
      modalText += `Section: ${ticket.Section}`;
      modalText += ' | ';
      modalText += `Row: ${ticket.Row}`;
      modalText += ' | ';
      modalText += `Seat: ${ticket.Seat}`;
      modalText += '\n';
    }
    return modalText;
  }

  ticketTemplateSelection() {
    swal({
      title: 'Do you want to use a ticket template?',
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
          this.ticketTemplateUse = true;
        }
        else{
          this.ticketTemplateUse = false;
        }
        this.initNewTickets(this.ticketTemplateUse);
        this.openModal('newTicketsModal', this.newTicketsModalElement);
      });
  }

  initNewTickets(isUsed) {

    if(!isUsed)
    {
      this.loadInitTicketInformation();
      this.newTickets.push(this.newTicket);
    }
    else
    {
      if(this.prefillTemplate){
        this.loadPrefillTemplateInformation();
      }
      else{
        this.loadInitTemplateInformation();
      }
    }
  }

  loadInitTicketInformation(){
    if (this.newTickets.length < 1) {
      this.checkForActiveFilters();
    }

    this.newTicketIndex++;
    this.newTicket = new Ticket();

    this.newTicket.newTicketIndex = this.newTicketIndex;
    this.newTicket.AutoApprove = 'N';
    this.newTicket.FoodOrdering = 'N';
    this.newTicket.TicketStatus = 'AVAILABLE';
    this.newTicket.TicketBatchStatus = 'AVAILABLE';
    this.newTicket.TicketDeliveryType = 'ELECTRONIC';

    if (this.newTicketIndex === 2) {
      this.preFillActive = true;
    }

    if (this.preFillActive && this.newTicketIndex > 1) {
      this.newTicket.AssociatedVenueNumber = this.newTickets[0].AssociatedVenueNumber;
      this.newTicket.AvailableTo = this.newTickets[0].AvailableTo;
      //this.newTicket.TicketDeliveryType = this.newTickets[0].TicketDeliveryType;
      this.newTicket.AutoApprove = this.newTickets[0].AutoApprove;
      this.newTicket.AssociatedSeatType = this.newTickets[0].AssociatedSeatType;
      this.newTicket.Section = this.newTickets[0].Section;
      this.newTicket.Row = this.newTickets[0].Row;
    }

    this.newTicket['formctrl_Section'] = new FormControl('', Validators.required);
    this.newTicket['formctrl_Section'].setValue(this.newTicket.Section);
    this.newTicket['formctrl_Row'] = new FormControl('', Validators.required);
    this.newTicket['formctrl_Row'].setValue(this.newTicket.Row);
    this.newTicket['formctrl_Seat'] = new FormControl('', Validators.required);

    if (this.selEvent) {
      this.newTicket.AssociatedEventNumber = this.selEvent.EventNumber;
    }

    if (this.selEventCategory) {
      this.newTicket.AssociatedTicketCategory = this.selEventCategory.Category;
    }
    //console.log("From ticket----", this.newTickets);
  }

  loadInitTemplateInformation(){
    this.newTicketIndex++;
    this.newTicket = new Ticket();

    this.newTicket.newTicketIndex = this.newTicketIndex;
    this.newTicket.AutoApprove = 'N';
    this.newTicket.FoodOrdering = 'N';
    this.newTicket.TicketStatus = 'AVAILABLE';
    this.newTicket.TicketBatchStatus = 'AVAILABLE';

    this.newTicket['formctrl_Section'] = new FormControl('', Validators.required);
    this.newTicket['formctrl_Section'].setValue(this.newTicket.Section);
    this.newTicket['formctrl_Row'] = new FormControl('', Validators.required);
    this.newTicket['formctrl_Row'].setValue(this.newTicket.Row);
    this.newTicket['formctrl_Seat'] = new FormControl('', Validators.required);
  }

  loadPrefillTemplateInformation(){
    this.selEventCategory = this.eventService.eventCategories[this.eventService.eventCategories.findIndex(c => c.Category === this.prefillTemplate.AssociatedTicketCategory)];

    this.newTickets = [];
    this.prefillTemplate.tickets.forEach((ticket, index) => {
      this.insertPrefillTemplateTicketsToNewTickets(ticket, index);
    });
    this.newTicket = this.newTickets[0]
    //console.log("From template----", this.newTickets);
  }

  insertPrefillTemplateTicketsToNewTickets(item, i){
    var t = new Ticket();
    t.newTicketIndex = i;

    t.AssociatedVenueNumber = this.prefillTemplate.AssociatedVenueNumber;
    t.AutoApprove = item.AutoApprove === 1 ? 'Y' : 'N';
    t.FoodOrdering = 'N';
    t.TicketStatus = 'AVAILABLE';
    t.TicketBatchStatus = 'AVAILABLE';

    t.AssociatedSeatType = item.AssociatedSeatType;
    t.TicketDeliveryType = 'ELECTRONIC'; //t.TicketDeliveryType = item.AssociatedDeliveryType;
    t.AvailableTo = item.AvailableTo;
    t['formctrl_Section'] = new FormControl('', Validators.required);
    t['formctrl_Section'].setValue(item.Section);
    t['formctrl_Row'] = new FormControl('', Validators.required);
    t['formctrl_Row'].setValue(item.Row);
    t['formctrl_Seat'] = new FormControl('', Validators.required);
    t['formctrl_Seat'].setValue(item.Seat);

    this.newTickets.push(t);
  }

  checkForActiveFilters() {
    if (this.filterEvent != null) {

      this.selEvent = this.filterEvent;

      const event = this.eventService.events[this.eventService.events.findIndex(
        e => e.EventNumber === this.selEvent.EventNumber
      )];

      this.selEventCategory = new Category();
      this.selEventCategory.Category = event.AssociatedTicketCategory;
    } else if (this.filterCategory != null) {
      this.selEventCategory = new Category();
      this.selEventCategory.Category = this.filterCategory;
    }
  }

  initTicketEditing() {

    // const parentEvent = this.eventService.events[0];
    //console.log(parentEvent);
    //console.log(this.selectedTickets);
    //console.log(this.eventService.events)
    // this.selEvent = parentEvent;
    // this.selEventDescription = parentEvent.EventDescription;
    // this.selEventDateTime = parentEvent.EventDateTime;

    this.selEvent = this.eventService.events[this.eventService.events.findIndex(
      e => e.EventNumber === this.selectedTickets[0].AssociatedEventNumber)];
    //console.log(this.selEvent);

    this.selEventCategory = this.eventService.eventCategories[this.eventService.eventCategories.findIndex(
      e => e.Category === this.selectedTickets[0].AssociatedTicketCategory)];

    for (let i = 0; i < this.selectedTickets.length; i++) {

      // Save copying
      this.editedTickets.push(
        JSON.parse(
          JSON.stringify(this.selectedTickets[i])
        )
      );
    }

    for (let i = 0; i < this.editedTickets.length; i++) {
      // Initialize form controls
      this.editedTickets[i]['formctrl_Section'] = new FormControl('', Validators.required);
      this.editedTickets[i]['formctrl_Section'].setValue(this.editedTickets[i].Section);
      this.editedTickets[i]['formctrl_Row'] = new FormControl('', Validators.required);
      this.editedTickets[i]['formctrl_Row'].setValue(this.editedTickets[i].Row);
      this.editedTickets[i]['formctrl_Seat'] = new FormControl('', Validators.required);
      this.editedTickets[i]['formctrl_Seat'].setValue(this.editedTickets[i].Seat);
    }
  }

  private setNewTicketIndexes() {
    for (let i = 0; i < this.newTickets.length; i++) {
      this.newTickets[i].newTicketIndex = i + 1;
    }
    this.newTicketIndex = this.newTickets.length;
  }

  ticketDetailInputChange(input: string, index: number, field: string) {
    switch (field) {
      case 'Section':
        this.newTickets[index - 1].Section = input;
        break;
      case 'Row':
        this.newTickets[index - 1].Row = input;
        break;
      case 'Seat':
        this.newTickets[index - 1].Seat = input;
        break;
    }
    if (this.preFillActive && index === 1) {
      for (let i = 1; i < this.newTickets.length; i++) {
        switch (field) {
          case 'AssociatedVenueNumber':
            this.newTickets[i].AssociatedVenueNumber = this.newTickets[0].AssociatedVenueNumber;
            break;
          case 'AvailableTo':
            this.newTickets[i].AvailableTo = this.newTickets[0].AvailableTo;
            break;
          case 'AutoApprove':
            this.newTickets[i].AutoApprove = this.newTickets[0].AutoApprove;
            break;
          case 'SeatType':
            this.newTickets[i].AssociatedSeatType = this.newTickets[0].AssociatedSeatType;
            break;
          // case 'DeliveryType':
          //   this.newTickets[i].TicketDeliveryType = this.newTickets[0].TicketDeliveryType;
          //   break;
          case 'Section':
            this.newTickets[i].Section = input;
            this.newTickets[i].formctrl_Section.setValue(input);
            break;
          case 'Row':
            this.newTickets[i].Row = input;
            this.newTickets[i].formctrl_Row.setValue(input);
            break;
        }
      }
    }
  }

  reloadChangedTicketInformation(t){
    this.newTicket = t;
    //console.log(this.newTicket)
  }

  removeTicket(index) {
    if (this.newTickets.length > 1) {
      for (let i = 0; i < this.newTickets.length; i++) {
        if (this.newTickets[i].newTicketIndex == index) {
          this.newTickets.splice(i, 1);
          this.setNewTicketIndexes();
          return;
        }
      }
    }
  }

  addTicket() {
    if (this.modalTicketsValid(this.newTickets)) {
      //console.log("post>>>>>>>>>", this.newTickets);
      for (let i = 0; i < this.newTickets.length; i++) {

        // Copy form ctrl values to ticket obj
        this.newTickets[i].Section = this.newTickets[i].formctrl_Section.value;
        this.newTickets[i].Row = this.newTickets[i].formctrl_Row.value;
        this.newTickets[i].Seat = this.newTickets[i].formctrl_Seat.value;

        this.ticketService.addNewTicket(this.newTickets[i]).subscribe(
          () => {
            if (i === this.newTickets.length - 1) { // LAST
              this.filterEvent = null;
              this.filterCategory = null;
              this.ticketService.getTickets();
              this.newTicketsModal.close();
              swal('Success!', 'Ticket(s) successfully created.', 'success');
            }
            this.loader.hide();
          },
          err => {
            if (i === this.newTickets.length - 1) { // LAST
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

  modalTicketsValid(tickets): boolean {
    let valid = true;

    if(!this.selEvent){
      valid = false;
    }

    for (let i = 0; i < tickets.length; i++) {
      if (!tickets[i].AssociatedEventNumber || tickets[i].AssociatedEventNumber == 0) {
        valid = false;
      }
      if (!tickets[i].AssociatedTicketCategory) {
        valid = false;
      }
      if (!tickets[i].AssociatedSeatType) {
        valid = false;
      }
      // if (!tickets[i].TicketDeliveryType) {
      //   valid = false;
      // }
      if (!tickets[i].AvailableTo) {
        valid = false;
      }
      if (!tickets[i].AutoApprove) {
        valid = false;
      }
      if (!tickets[i].formctrl_Section.valid) {
        valid = false;
      }
      if (!tickets[i].formctrl_Row.valid) {
        valid = false;
      }
      if (!tickets[i].formctrl_Seat.valid) {
        valid = false;
      }

      if (!valid) {
        this.selectTicketDetails(tickets[i].newTicketIndex);
        break;
      }
    }
    return valid;
  }

  selectTicketDetails(newTicketIndex) {
    this.selTicketIndex = newTicketIndex;
  }

  pushTicketToSelected(ticket) {
    const index = this.selectedTickets.map(function (e) {
      return e.TicketRecordNumber;
    }).indexOf(ticket['TicketRecordNumber']);

    if (index > -1) {
      this.selectedTickets.splice(index, 1);

      if (this.filterEvent == null) {
        this.selEvent = null;
      }

      if (this.filterCategory == null) {
        this.selEventCategory = null;
      }
    } else {
      this.selectedTickets.push(ticket);
    }

    ticket['selected'] = !ticket['selected'];
  }

  private checkForSameEvent() {
    for (let i = 0; i < this.selectedTickets.length - 1; i++) {
      const lastAddedTicket = this.selectedTickets[this.selectedTickets.length - 1];
      if (this.selectedTickets[i].AssociatedEventNumber != lastAddedTicket.AssociatedEventNumber) {
        this.selectForDeletionOnly = true;
        return;
      }
    }
    this.selectForDeletionOnly = false;
  }

  selectTicket(ticket) {
    this.prepareTicketForEditing(ticket);
    this.pushTicketToSelected(ticket);
    this.checkForSameEvent();
  }

  prepareTicketForEditing(ticket) {
    this.newTicketIndex++;
    ticket['newTicketIndex'] = this.newTicketIndex;
    // Handle deleted events
    const event = this.eventService.events[this.eventService.events.findIndex(
      e => e.EventNumber === ticket.AssociatedEventNumber
    )];
    // if (!event) {
    //   ticket.AssociatedEventNumber = null;
    // }
  }

  selectAll() {
    this.actionSelectAll = !this.actionSelectAll;
    for (let i = 0; i < this.ticketService.tickets.length; i++) {
      const ticket = this.ticketService.tickets[i];
      let hidden = false;
      if (this.filterEvent && ticket.AssociatedEventNumber !== this.filterEvent.EventNumber) {
        hidden = true;
      }
      if (this.filterCategory && ticket.AssociatedTicketCategory !== this.filterCategory) {
        hidden = true;
      }

      if (!hidden) {
        const index = this.selectedTickets.map(function (t) {
          return t.TicketRecordNumber;
        }).indexOf(ticket['TicketRecordNumber']);
        if (index > -1 && !this.actionSelectAll) {
          this.selectedTickets.splice(index, 1);
          ticket['selected'] = 0;
          this.newTicketIndex = 0;
          if (this.filterEvent == null) {
            this.selEvent = null;
          }
          if (this.filterCategory == null) {
            this.selEventCategory = null;
          }
        } else if (index === -1 && this.actionSelectAll) {
          this.selectedTickets.push(ticket);
          ticket['selected'] = true;
          this.prepareTicketForEditing(ticket);
        }
      }
    }
    this.checkForSameEvent();
  }

  private calcPageNbr(i, itemsPerPage, pageNbr, absoluteIndex) {
    if (!absoluteIndex && i < 0 ||
      absoluteIndex && i < (itemsPerPage * pageNbr - itemsPerPage) && pageNbr > 1) {
      pageNbr--;
    } else if (!absoluteIndex && i > itemsPerPage - 1 ||
      absoluteIndex && i > (itemsPerPage * pageNbr - 1)) {
      pageNbr++;
    }
    return pageNbr;
  }

  selectModalEditedTicket(i, isAbsoluteIndex: Boolean = true, directSelection: Boolean = false) {
    if (!directSelection) {
      this.editedTicketsPageNbr = this.calcPageNbr(i, this.editedTicketsItemsPerPage, this.editedTicketsPageNbr, isAbsoluteIndex);
    }
    if (!isAbsoluteIndex) {
      const absoluteIndex = this.editedTicketsPageNbr * this.editedTicketsItemsPerPage - this.editedTicketsItemsPerPage + i;
      this.selTicketIndex = this.selectedTickets[absoluteIndex].newTicketIndex;
    } else {
      this.selTicketIndex = this.selectedTickets[i].newTicketIndex;
    }
  }

  selectModalNewTicket(i, isAbsoluteIndex: Boolean = true, directSelection: Boolean = false) {
    if (!directSelection) {
      this.newTicketsPageNbr = this.calcPageNbr(i, this.newTicketsItemsPerPage, this.newTicketsPageNbr, isAbsoluteIndex);
    }
    if (!isAbsoluteIndex) {
      const absoluteIndex = this.newTicketsPageNbr * this.newTicketsItemsPerPage - this.newTicketsItemsPerPage + i;
      this.selTicketIndex = this.newTickets[absoluteIndex].newTicketIndex;
    } else {
      this.selTicketIndex = this.newTickets[i].newTicketIndex;
    }
  }
}
