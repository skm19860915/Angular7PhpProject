import { Component, OnInit } from '@angular/core';
import { FormControl, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { DatePipe } from '@angular/common';
import { Event } from '../services/observables/event';
import { EventService } from '../services/event.service';
import { TicketService } from '../services/ticket.service';
import { RequestService } from '../services/request.service';
import { UserService } from '../services/user.service';
import { NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { LoaderService } from '../services/loader/loader.service';
declare var $: any;

// Dirty workaround
// See: https://github.com/t4t5/sweetalert/issues/799
import * as _swal from 'sweetalert';
import { SweetAlert } from 'sweetalert/typings/core';
import { AssignmentService } from '../services/assignment.service';
import { VenueService } from '../services/venue.service';
const swal: SweetAlert = _swal as any;

@Component({
  selector: 'app-overview',
  templateUrl: './overview.component.html',
  styleUrls: ['./overview.component.css']
})
export class OverviewComponent implements OnInit {

  itemsPerPageTickets = 12;
  usageReasons = [];

  selectedTickets = [];
  selectedTicketsFormControls = [];
  selectedEvent;
  selectedEventIndex = -1;
  selectedTicketAddInfo;
  userForDirectAssignment;
  eventList: Event[] = [];
  categoryList = [];
  rawEventList: Event[] = [];
  eventFilter;
  eventsSortBy = 'Date/Category';
  preFillActive = false;
  guestDetailsModal: NgbModalRef;
  filterByModal: NgbModalRef;
  addInfoModal: NgbModalRef;
  selectClientMatterCountByModal: NgbModalRef;
  ticketsPageNbr = 1;
  eventsPageNbr = 1;
  guestDetailsPageNbr = 1;
  guestDetailsItemsPerPage = 5;
  actionSelectAll = false;
  assignmentMode = false;
  guestDetailsCurrentSelectedRecordNbr = -1;
  guestDetailsCurrentSelectedTicketIndex = -1;
  maxEventsPerPage = 10;
  keyword = 'fldClientMatter';
  recordCount = 50;
  pageNumber = 1;

  constructor(
    public eventService: EventService,
    public ticketService: TicketService,
    public requestService: RequestService,
    public assignmentService: AssignmentService,
    public userService: UserService,
    public venueService: VenueService,
    public datePipe: DatePipe,
    private router: Router,
    private modalService: NgbModal,
    private loaderService: LoaderService
  ) { }

  ngOnInit() {
    this.eventService.returnEventsWithVenueInfo().subscribe(
      data => this.fetchEventData(data),
      err => {
        console.error(err);
        this.loaderService.hide();
      }
    );
    this.venueService.getAllEventVenues();
  }

  checkType(value){
    if (typeof value === 'string'){
      return null;
    }
    return value;
  }

  onChangeSearch(val: string) {
  }

  onFocused(e){
  }

  getCategoryThumb(category) {
    switch (category) {
      case 'CAVS':
        return 'assets/event_icons/logoCavs_small.png';
      case 'BROWNS':
        return 'assets/event_icons/logoBrowns_small.png';
      case 'INDIANS':
        return 'assets/event_icons/logoIndians_small.png';
      case 'MONSTERS':
        return 'assets/event_icons/logoMonsters_small.png';
      case 'CONCERTS':
        return 'assets/event_icons/logoConcert_small.png';
      case 'PERFORMING ARTS':
        return 'assets/event_icons/logoPerformingArts_small.png';
      case 'WH EVENT':
        return 'assets/event_icons/logoWH_small.png';
      default:
        return 'assets/event_icons/logoWH_small.png';
    }
  }

  fetchEventData(data) {

    for (let i = 0; i < data.length; i++) {
      // Need to copy datePipe to each object in array
      // to use it later on in the sort() function
      data[i]['datePipe'] = this.datePipe;

      switch (data[i]['AssociatedTicketCategory'].trim()) {
        case 'CAVS':
          data[i]['thumbnail'] = 'assets/event_icons/logoCavs_small.png';
          break;
        case 'BROWNS':
          data[i]['thumbnail'] = 'assets/event_icons/logoBrowns_small.png';
          break;
        case 'INDIANS':
          data[i]['thumbnail'] = 'assets/event_icons/logoIndians_small.png';
          break;
        case 'MONSTERS':
          data[i]['thumbnail'] = 'assets/event_icons/logoMonsters_small.png';
          break;
        case 'CONCERTS':
          data[i]['thumbnail'] = 'assets/event_icons/logoConcert_small.png';
          break;
        case 'PERFORMING ARTS':
          data[i]['thumbnail'] = 'assets/event_icons/logoPerformingArts_small.png';
          break;
        case 'WH EVENT':
          data[i]['thumbnail'] = 'assets/event_icons/logoWH_small.png';
          break;
        default:
          data[i]['thumbnail'] = 'assets/event_icons/logoWH_small.png';
          break;
      }

      if (data[i].Status === 'AVAILABLE') {
        if (this.categoryList.indexOf(data[i]['AssociatedTicketCategory'].trim()) === -1) {
          this.categoryList.push(data[i]['AssociatedTicketCategory'].trim());
        }
        // Only add available events
        this.eventList.push(data[i]);
        this.rawEventList.push(data[i]);
      }
    }

    this.sortEventList();
    this.loaderService.hide();
  }

  filterEventList() {
    this.eventList = [];
    this.rawEventList.forEach(event => {

      if (this.passFilters(event)) {
        this.eventList.push(event);
      }
    });

    this.sortEventList();
  }

  passFilters(event) {
    if (event.Status !== 'AVAILABLE') { return false; }
    if (this.eventFilter && event.AssociatedTicketCategory !== this.eventFilter) { return false; }
    return true;
  }

  sortEventList() {
    switch (this.eventsSortBy) {
      case 'Date/Category':
        this.eventList = this.eventList.sort(function (e1, e2) {
          // let e1Cat = e1.AssociatedTicketCategory.toLowerCase();
          const e1Date = e1['datePipe'].transform(e1.EventDateTime['date'].replace(' ', 'T').split('.')[0], 'yyyy-MM-dd HH:mm:ss');
          // let e2Cat = e2.AssociatedTicketCategory.toLowerCase();
          const e2Date = e2['datePipe'].transform(e2.EventDateTime['date'].replace(' ', 'T').split('.')[0], 'yyyy-MM-dd HH:mm:ss');
          if (e1Date < e2Date) {
            return -1;
          } else if (e1Date > e2Date) {
            return 1;
          }
          return 0;
        });
        return;
      case 'Category/Date':
        this.eventList = this.eventList.sort(function (e1, e2) {
          const e1Cat = e1.AssociatedTicketCategory.toLowerCase();
          const e1Date = e1['datePipe'].transform(e1.EventDateTime['date'].replace(' ', 'T').split('.')[0], 'yyyy-MM-dd HH:mm:ss');
          const e2Cat = e2.AssociatedTicketCategory.toLowerCase();
          const e2Date = e2['datePipe'].transform(e2.EventDateTime['date'].replace(' ', 'T').split('.')[0], 'yyyy-MM-dd HH:mm:ss');
          if (e1Cat < e2Cat) {
            return -1;
          } else if (e1Cat > e2Cat) {
            return 1;
          }
          return 0;
        });
        return;
    }
  }

  transferUserSelection(user) {
    this.userForDirectAssignment = user;
  }

  openModal(modal, content) {
    switch (modal) {
      case 'guestDetailsModal':
        this.guestDetailsModal = this.modalService.open(content, { size: 'lg', backdrop: 'static' });
        this.guestDetailsModal.result.then(() => {
          this.assignmentMode = false;
        }, () => {
        });
        break;
      case 'filterByModal':
        this.filterByModal = this.modalService.open(content, { backdrop: 'static' });
        this.filterByModal.result.then(() => {
        }, () => {
        });
        break;
      case 'addInfoModal':
        this.addInfoModal = this.modalService.open(content, { backdrop: 'static' });
        this.addInfoModal.result.then(() => {
          this.selectedTicketAddInfo = null;
        }, () => {
          this.selectedTicketAddInfo = null;
        });
        break;
      case 'selectClientMatterCountByModal':
        this.selectClientMatterCountByModal = this.modalService.open(content, { backdrop: 'static' });
        this.selectClientMatterCountByModal.result.then(() => {
        }, () => {
        });
        break;
    }
  }

  private selectAll() {
    this.actionSelectAll = !this.actionSelectAll;
    for (let i = 0; i < this.ticketService.tickets.length; i++) {
      const index = this.selectedTickets.map(function (e) {
        return e.TicketRecordNumber;
      }).indexOf(this.ticketService.tickets[i]['TicketRecordNumber']);
      if (index === -1 && this.actionSelectAll) {
        this.selectTicket(this.ticketService.tickets[i]);
      } else if (index > -1 && !this.actionSelectAll) {
        this.deselectTicket(this.ticketService.tickets[i]);
      }
    }
    this.autoActivatePreFill();
  }

  private selectTicket(ticket) {
    if (this.requestPermitted(ticket.AvailableTo)) {
      if (ticket['Status'] == 'AVAILABLE' || ticket['Status'] == 'PENDING REQUESTS') {
        this.selectedTickets.push(ticket);
        this.selectedTickets[this.selectedTickets.length - 1]['formctrl_guestName'] = new FormControl('', Validators.required);
        this.selectedTickets[this.selectedTickets.length - 1]['formctrl_guestEmail'] = new FormControl();
        this.selectedTickets[this.selectedTickets.length - 1]['formctrl_guestCompany'] = new FormControl('', Validators.required);
        this.selectedTickets[this.selectedTickets.length - 1]['formctrl_guestNotes'] = new FormControl();
        ticket['selected'] = !ticket['selected'];
      }
    }
  }

  private deselectTicket(ticket) {
    const index = this.selectedTickets.map(function (e) {
      return e.TicketRecordNumber;
    }).indexOf(ticket['TicketRecordNumber']);
    this.selectedTickets.splice(index, 1);
    ticket['selected'] = !ticket['selected'];
  }

  private clearSelection() {
    this.selectedTickets.map((item)=>{
      item['showClientMatter'] = false;
      item['fldClientMatter'] = '';
    })
    this.selectedTickets = [];
    this.ticketService.tickets.forEach(t => {
      t.selected = 0;
      t.ReasonCode = null;
      t.GuestName = null;
      t.GuestEmail = null;
      t.GuestCompany = null;
      t.RequestNotes = null;
    });
    this.actionSelectAll = false;
    this.guestDetailsPageNbr = 1;
    this.guestDetailsCurrentSelectedRecordNbr = -1;
    this.guestDetailsCurrentSelectedTicketIndex = -1;
    this.userForDirectAssignment = null;
  }

  private selectEvent(event) {
    this.ticketsPageNbr = 1;
    this.selectedTickets = [];
    this.actionSelectAll = false;
    this.selectedEvent = event['EventDescription'];
    this.selectedEventIndex = this.getEventIndexFromNbr(event.EventNumber);
    this.ticketService.getTickets(event['EventNumber']);
    this.requestService.getTicketRequests();
  }

  private getEventIndexFromNbr(eventNbr) {
    return this.eventList.findIndex(e => e.EventNumber === eventNbr);
  }

  private guestDetailInputChange(index: number, key: string, form_ctrl?: string, val?: string) {
    if (this.preFillActive && index === 0) {
      for (let i = 0; i < this.selectedTickets.length; i++) {
        if (val) {
          this.selectedTickets[i][key] = val;
          if (i > 0 && form_ctrl) {
            this.selectedTickets[i][form_ctrl].setValue(val);
          }
        } else {
          this.selectedTickets[i][key] = this.selectedTickets[0][key];
          if (i > 0 && form_ctrl) {
            this.selectedTickets[i][form_ctrl].setValue(this.selectedTickets[0][form_ctrl].value);
          }
        }
      }
    }
  }

  /**
   * This function ensures that the binded variable gets
   * cleared if the user cleares the input with the delete button
   *
   * @param i index of the ticket
   * @param key key of the ticket's field
   * @param val value from the input field
   */
  private inputChanged(i: number, key: string, val: string) {
    if (val === '') {
      this.selectedTickets[i][key] = '';
    }
  }

  autoFillGuestDetails(i: number, key: string, val: string) {
    //console.log(i + ", " + key + ", " + val)

    if (key === 'ReasonCode' && val === 'RAFFLE') {
      if (!this.selectedTickets[i]['formctrl_guestName'].valid) {
        this.selectedTickets[i]['formctrl_guestName'].setValue('EMPLOYEE/GUEST');
        this.selectedTickets[i]['GuestName'] = 'EMPLOYEE/GUEST';
        if (i === 0) {
          this.guestDetailInputChange(i, 'GuestName', 'formctrl_guestName');
        }
      }
      if (!this.selectedTickets[i]['formctrl_guestCompany'].valid) {
        this.selectedTickets[i]['formctrl_guestCompany'].setValue('WH');
        this.selectedTickets[i]['GuestCompany'] = 'WH';
        if (i === 0) {
          this.guestDetailInputChange(i, 'GuestCompany', 'formctrl_guestCompany');
        }
      }
    }

    if (key === 'ReasonCode' && val === 'EXISTING CLIENT'){
      this.selectedTickets[i]['showClientMatter'] = true;
    }
    else{
      this.selectedTickets[i]['showClientMatter'] = false;
    }
  }

  private clearEventSelection() {
    this.selectedEvent = null;
    this.selectedEventIndex = -1;
  }

  private changeTicketSelectStatus(ticket) {
    const index = this.selectedTickets.map(function (e) {
      return e.TicketRecordNumber;
    }).indexOf(ticket['TicketRecordNumber']);
    if (index > -1) {
      this.deselectTicket(ticket);
    } else {
      this.selectTicket(ticket);
    }
    this.autoActivatePreFill();
  }

  private autoActivatePreFill() {
    // Auto activate prefill if more tickets are selected
    if (this.selectedTickets.length > 1) {
      this.preFillActive = true;
    } else {
      this.preFillActive = false;
    }
  }

  private setCategoryFilter(cat: string) {
    if (this.eventFilter === cat) {
      this.eventFilter = null;
    } else {
      this.eventFilter = cat;
    }
  }

  private fetchUsageReasonData(data) {
    this.usageReasons = data;
  }

  public addTicketRequest() {

    let ticketInformationValid = true;

    this.selectedTickets.forEach((newTicketRequest) => {
      if (!this.ticketGuestDetailsValid(newTicketRequest)) {
        swal('Error!', 'Guest details are invalid.', 'error');
        ticketInformationValid = false;
      }
    });

    //console.log(this.selectedTickets)

    if (ticketInformationValid) {
      for (let i = 0; i < this.selectedTickets.length; i++) {

        // Get data from form controls
        this.selectedTickets[i]['GuestName'] = this.selectedTickets[i]['formctrl_guestName'].value;
        this.selectedTickets[i]['GuestEmail'] = this.selectedTickets[i]['formctrl_guestEmail'].value;
        this.selectedTickets[i]['GuestCompany'] = this.selectedTickets[i]['formctrl_guestCompany'].value;
        this.selectedTickets[i]['RequestNotes'] = this.selectedTickets[i]['formctrl_guestNotes'].value;
        this.selectedTickets[i]['TicketDeliveryType'] = 'ELECTRONIC';

        // If ticket gets directly assigned, we use the auto approve mechanism
        if (this.assignmentMode) {
          this.selectedTickets[i]['AutoApprove'] = 'Y';
          this.selectedTickets[i]['RequestedBy'] = this.userForDirectAssignment.userAccount;
          this.selectedTickets[i]['FirmAttorneyForGuest'] = this.userForDirectAssignment.userAccount;
        } else {
          this.selectedTickets[i]['CreatedBy'] = this.userService.curUser.userAccount;
          this.selectedTickets[i]['RequestedBy'] = this.userService.selUser.userAccount;
          this.selectedTickets[i]['FirmAttorneyForGuest'] = this.userService.selUser.userAccount;
        }

        // THIS NEEDS TO BE AUTO GENERATED IN DATABASE/BACKEND
        const requestDateTimeObj = {};
        const date = new Date();
        requestDateTimeObj['date'] = date.toISOString;
        requestDateTimeObj['timezone'] = 'UTC';
        requestDateTimeObj['timezone_type'] = date.getTimezoneOffset;
        this.selectedTickets[i]['RequestDateTime'] = requestDateTimeObj;

        if(this.selectedTickets[i]['fldClientMatter']){
          if(this.selectedTickets[i]['ReasonCode'] && this.selectedTickets[i]['ReasonCode'] === 'EXISTING CLIENT'){
            this.selectedTickets[i]['fldClientMatter'] = this.selectedTickets[i]['fldClientMatter'].fldClientMatter;
          }
          else{
            this.selectedTickets[i]['fldClientMatter'] = '';
          }
        }
        //console.log(this.selectedTickets);

        this.requestService.addTicketRequest(this.selectedTickets[i]).subscribe(
          () => {
            if (i == this.selectedTickets.length - 1) { //LAST
              this.guestDetailsModal.close();
              this.clearSelection();
              swal('Success!', 'Ticket(s) were successfully requested.', 'success').then((value) => {
                this.router.navigateByUrl('/requests');
              });
            }
            this.loaderService.hide();
          },
          err => {
            if (i == this.selectedTickets.length - 1) { //LAST
              swal('Error!', 'An error occured while saving the new ticket request(s).', 'error');
              console.error(err);
            }
            this.loaderService.hide();
          }
        );
      }
    }
  }

  addSpecialTicketRequest() {
    const event = this.eventList[this.selectedEventIndex];
    const specialReqEventDateTime = this.datePipe.transform(event.EventDateTime['date'].replace(' ', 'T').split('.')[0], 'M/d/y @ h:mm a');
    this.router.navigateByUrl(`/special-requests?action=add&title=${event.EventDescription}, ${specialReqEventDateTime}`);
  }

  private managePageNbr(i, itemsPerPage, pageNbr, absoluteIndex) {
    if (!absoluteIndex && i < 0 ||
      absoluteIndex && i < (itemsPerPage * pageNbr - this.guestDetailsItemsPerPage) && pageNbr > 1) {
      this.guestDetailsPageNbr--;
    } else if (!absoluteIndex && i > this.guestDetailsItemsPerPage - 1 ||
      absoluteIndex && i > (itemsPerPage * pageNbr - 1)) {
      this.guestDetailsPageNbr++;
    }
  }

  private selectTicketGuestDetails(i, isAbsoluteIndex: Boolean = true, directSelection: Boolean = false) {
    let prevTicket = this.selectedTickets.find(item=>item.TicketRecordNumber == this.guestDetailsCurrentSelectedRecordNbr);
    //console.log(prevTicket,this.selectedTickets[i])
    if(!this.selectedTickets[i]['ReasonCode']){
      this.selectedTickets[i]['showClientMatter'] = false;
    }
    else if(this.selectedTickets[i]['ReasonCode'] && this.selectedTickets[i]['ReasonCode'] === 'EXISTING CLIENT'){
      this.selectedTickets[i]['showClientMatter'] = true;
      this.selectedTickets[i].fldClientMatter = prevTicket.fldClientMatter;
    }
    else{
      this.selectedTickets[i]['showClientMatter'] = false;
    }
    if (!directSelection) {
      this.managePageNbr(i, this.guestDetailsItemsPerPage, this.guestDetailsPageNbr, isAbsoluteIndex);
    }
    if (!isAbsoluteIndex) {
      const absoluteIndex = this.guestDetailsPageNbr * this.guestDetailsItemsPerPage - this.guestDetailsItemsPerPage + i;
      this.guestDetailsCurrentSelectedRecordNbr = this.selectedTickets[absoluteIndex].TicketRecordNumber;
      this.guestDetailsCurrentSelectedTicketIndex = absoluteIndex;
    } else {
      this.guestDetailsCurrentSelectedRecordNbr = this.selectedTickets[i].TicketRecordNumber;
      this.guestDetailsCurrentSelectedTicketIndex = i;
    }
  }

  private ticketGuestDetailsValid(newTicketRequest): Boolean {
    //console.log(newTicketRequest);

    if (newTicketRequest['ReasonCode'] == null || newTicketRequest['ReasonCode'] == '') {
      return false;
    }
    // if (newTicketRequest['TicketDeliveryType'] == null || newTicketRequest['TicketDeliveryType'] == '') {
    //   return false;
    // }
    if (!newTicketRequest.formctrl_guestName.valid ||
        newTicketRequest.formctrl_guestName.value.toLowerCase() === 'guest' ||
        newTicketRequest.formctrl_guestName.value.toLowerCase() === 'na' ||
        newTicketRequest.formctrl_guestName.value.toLowerCase() === 'n/a' ||
        newTicketRequest.formctrl_guestName.value.toLowerCase() === 'unknown') {
      return false;
    }
    if (!newTicketRequest.formctrl_guestEmail.valid) {
      return false;
    }
    if (!newTicketRequest.formctrl_guestCompany.valid ||
        newTicketRequest.formctrl_guestCompany.value.toLowerCase() === 'guest' ||
        newTicketRequest.formctrl_guestCompany.value.toLowerCase() === 'na' ||
        newTicketRequest.formctrl_guestCompany.value.toLowerCase() === 'n/a' ||
        newTicketRequest.formctrl_guestCompany.value.toLowerCase() === 'unknown') {
      return false;
    }
    if (!newTicketRequest.formctrl_guestNotes.valid) {
      return false;
    }
    if (this.assignmentMode && !this.userForDirectAssignment) {
      return false;
    }
    if(newTicketRequest['showClientMatter']){
      if(newTicketRequest['fldClientMatter'] == null || newTicketRequest['fldClientMatter'] == '' || typeof newTicketRequest['fldClientMatter'] === 'string'){
        return false;
      }
    }

    return true;
  }

  private requestPermitted(availableTo): Boolean {
    switch (availableTo) {
      case 'Everyone':
        if (this.userService.selUser.memberOfEveryone.toString() != 'N' || this.userService.selUser.memberOfAssociate.toString() != 'N' || this.userService.selUser.memberOfPartner.toString() != 'N' || this.userService.selUser.memberOfSectionHead.toString() != 'N' || this.userService.selUser.memberOfTicketAdministration.toString() != 'N' || this.userService.selUser.memberOfSystemAdministration.toString() != 'N') {
          return true;
        }
        break;
      case 'Associate':
        if (this.userService.selUser.memberOfAssociate.toString() != 'N' || this.userService.selUser.memberOfPartner.toString() != 'N' || this.userService.selUser.memberOfSectionHead.toString() != 'N' || this.userService.selUser.memberOfTicketAdministration.toString() != 'N' || this.userService.selUser.memberOfSystemAdministration.toString() != 'N') {
          return true;
        }
        break;
      case 'Partner':
        if (this.userService.selUser.memberOfPartner.toString() != 'N' || this.userService.selUser.memberOfSectionHead.toString() != 'N' || this.userService.selUser.memberOfTicketAdministration.toString() != 'N' || this.userService.selUser.memberOfSystemAdministration.toString() != 'N') {
          return true;
        }
        break;
      case 'Section Head':
        if (this.userService.selUser.memberOfSectionHead.toString() != 'N' || this.userService.selUser.memberOfTicketAdministration.toString() != 'N' || this.userService.selUser.memberOfSystemAdministration.toString() != 'N') {
          return true;
        }
        break;
      case 'Administration':
        if (this.userService.selUser.memberOfTicketAdministration.toString() != 'N' || this.userService.selUser.memberOfSystemAdministration.toString() != 'N') {
          return true;
        }
        break;
    }

    return false;
  }

  private getFilterInformation(){
    let start = (this.pageNumber - 1) * this.recordCount;
    let end = this.pageNumber * this.recordCount;

    if(this.pageNumber == this.ticketService.pageCount){
      let cnt = Math.floor(this.ticketService.totalCount / this.recordCount);
      let realVal = this.ticketService.totalCount / this.recordCount;
      if(realVal > cnt){
        end = this.ticketService.totalCount;
      }
    }

    //console.log(start, end, this.ticketService.pageCount, this.pageNumber);

    this.ticketService.getFilterClientMatterData(start, end);
  }

  private getPageCount(num){
    this.pageNumber = 1;
    let count = Math.floor(this.ticketService.totalCount / num);
    let realValue = this.ticketService.totalCount / num;
    if(realValue > count){
      this.ticketService.pageCount = count + 1;
    }
    else{
      this.ticketService.pageCount = count;
    }
  }
}
