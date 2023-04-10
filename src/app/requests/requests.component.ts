import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { FormControl, Validators } from '@angular/forms';
import { RequestService } from '../services/request.service';
import { UserService } from '../services/user.service';
import { TicketService } from '../services/ticket.service';
import { DatePipe } from '@angular/common';
import { LoaderService } from '../services/loader/loader.service';

// Dirty workaround
// See: https://github.com/t4t5/sweetalert/issues/799
import * as _swal from 'sweetalert';
import { SweetAlert } from 'sweetalert/typings/core';
import { NgbModalRef, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { VenueService } from '../services/venue.service';
const swal: SweetAlert = _swal as any;

@Component({
  selector: 'app-requests',
  templateUrl: './requests.component.html',
  styleUrls: ['./requests.component.css']
})
export class RequestsComponent implements OnInit {

  @ViewChild('requestInfoModalObj') requestInfoModalRef: ElementRef;

  mobileSelectedRequestIndex = -1;
  editedRequest;
  editRequestModal: NgbModalRef;
  requestInfoModal: NgbModalRef;
  selectClientMatterCountByModal: NgbModalRef;
  pageNbr = 1;

  keyword = 'fldClientMatter';
  recordCount = 50;
  pageNumber = 1;

  constructor(
    public requestService: RequestService,
    public userService: UserService,
    public ticketService: TicketService,
    public venueService: VenueService,
    private datePipe: DatePipe,
    private modalService: NgbModal,
    private loader: LoaderService) { }

  ngOnInit() {
    if (this.venueService.venues.length === 0) {
      this.venueService.getAllEventVenues();
    }
    this.getTicketRequests();
  }

  onChangeSearch(val: string) {
  }

  onFocused(e){
  }

  openModal(modal, content) {
    switch (modal) {
      case 'editRequestModal':
        this.editRequestModal = this.modalService.open(content, { backdrop: 'static' });
        this.editRequestModal.result.then(() => {
          this.editedRequest = null;
        }, () => {
          this.editedRequest = null;
        });
        break;
      case 'requestInfoModal':
        this.requestInfoModal = this.modalService.open(content, { backdrop: 'static' });
        this.requestInfoModal.result.then(() => {
          //console.log("requestInfoModal closed");
        }, () => {
          //console.log("requestInfoModal closed");
        });
        break;
      case 'selectClientMatterCountByModal':
        this.selectClientMatterCountByModal = this.modalService.open(content, { backdrop: 'static', windowClass:'client-matter-modal' });
        this.selectClientMatterCountByModal.result.then(() => {
        }, () => {
        });
        break;
    }
  }

  openRequestDetails(requestNumber) {
    this.selectRequest(requestNumber).then(() => {
      this.requestInfoModal = this.modalService.open(this.requestInfoModalRef);
    });
  }

  selectRequest(requestNumber) {
    return new Promise((resolve) => {
      const i = this.getRequestIndex(requestNumber);
      if (this.requestService.ticketRequests[this.mobileSelectedRequestIndex]) {
        this.requestService.ticketRequests[this.mobileSelectedRequestIndex].selected = false;
      }

      if (this.mobileSelectedRequestIndex != i) {
        this.mobileSelectedRequestIndex = i;
        this.requestService.ticketRequests[i].selected = true;
      } else {
        this.mobileSelectedRequestIndex = -1;
      }

      resolve();
    });
  }

  private getTicketRequests(): void {
    if (this.requestService.ticketRequests.length > 0) {
      this.requestService.ticketRequests = [];
    }
    this.requestService.getTicketRequests();
  }

  private cancelRequest(i, mobile): void {
    if (!mobile) {
      i = this.getRequestIndex(i);
    }
    swal({
      title: 'You\'re about to cancel the following ticket request:',
      text: '\n' + this.requestService.ticketRequests[i].EventDescription
            + '\n' + this.datePipe.transform(this.requestService.ticketRequests[i].EventDateTime.date.replace(' ', 'T').split('.')[0], 'EEEE, M/d/y @ h:mm a')
            + '\nSection: ' + this.requestService.ticketRequests[i].Section
            + ' | Row: ' + this.requestService.ticketRequests[i].Row
            + ' | Seat: ' + this.requestService.ticketRequests[i].Seat
            + '\nFor User: ' + this.requestService.ticketRequests[i].RequestedBy,
      icon: 'warning',
      buttons: [true, true],
      dangerMode: true
    })
    .then((willCancel) => {
      if (willCancel) {
        this.requestService.cancelTicketRequest(i).subscribe(
          () => {
            this.mobileSelectedRequestIndex = -1;
            this.getTicketRequests();
            swal('Success!', 'Request was successfully canceled.', 'success');
            this.loader.hide();
          },
          err => {
            swal('Error!', 'An error occured while canceling the ticket request.', 'error');
            console.error(err);
            this.loader.hide();
          }
        );
      }
    });
  }

  getRequestIndex(requestNbr) {
    return this.requestService.ticketRequests.findIndex(r => r.RequestNumber === requestNbr);
  }

  private editRequest(i, mobile) {

    let requestIndex = i;

    if (!mobile) {
      requestIndex = this.getRequestIndex(i);
    }

    // Save copying
    this.editedRequest = JSON.parse(JSON.stringify(this.requestService.ticketRequests[requestIndex]));

    if (!this.editedRequest['formctrl_guestName']) {
      this.editedRequest['formctrl_guestName'] = new FormControl('', Validators.required);
    }
    if (!this.editedRequest['formctrl_guestEmail']) {
      this.editedRequest['formctrl_guestEmail'] = new FormControl();
    }
    if (!this.editedRequest['formctrl_guestCompany']) {
      this.editedRequest['formctrl_guestCompany'] = new FormControl('', Validators.required);
    }
    if (!this.editedRequest['formctrl_guestNotes']) {
      this.editedRequest['formctrl_guestNotes'] = new FormControl();
    }

    this.editedRequest['formctrl_guestName'].setValue(this.editedRequest.GuestName);
    this.editedRequest['formctrl_guestEmail'].setValue(this.editedRequest.GuestEmail);
    this.editedRequest['formctrl_guestCompany'].setValue(this.editedRequest.GuestCompany);
    this.editedRequest['formctrl_guestNotes'].setValue(this.editedRequest.RequestNotes);

    //console.log(this.editedRequest);
  }

  private updateRequest(): void {

    if (!this.updatedGuestDetailsValid()) {
      swal('Error!', 'Guest details are invalid.', 'error');
    }
    else {
      // Copy data from form controls
      this.editedRequest.GuestName = this.editedRequest.formctrl_guestName.value;
      this.editedRequest.GuestEmail = this.editedRequest.formctrl_guestEmail.value;
      this.editedRequest.GuestCompany = this.editedRequest.formctrl_guestCompany.value;
      this.editedRequest.RequestNotes = this.editedRequest.formctrl_guestNotes.value;
      //console.log(this.editedRequest.ClientMatter);
      if(this.editedRequest.ClientMatter){
        this.editedRequest.ClientMatter = this.editedRequest.ClientMatter.fldClientMatter;
      }

      if(this.editedRequest['ReasonCode'] != null && this.editedRequest['ReasonCode'] != 'EXISTING CLIENT'){
        this.editedRequest['ClientMatter'] = '';
      }
      //console.log(this.editedRequest);

      this.requestService.updateTicketRequest(this.editedRequest).subscribe(
        () => {
          this.getTicketRequests();
          this.editRequestModal.close();
          this.editedRequest = null;
          this.mobileSelectedRequestIndex = -1;
          swal('Success!', 'Request was successfully updated.', 'success');
          this.loader.hide();
        },
        err => {
          swal('Error!', 'An error occured while updating the ticket request.', 'error');
          console.error(err);
          this.loader.hide();
        }
      );
    }
  }

  private saveClinetMatter(e): void {
    this.editedRequest['ClientMatter'] = e.target.value.split("|", 1)[0].trim();
    //console.log(this.editedRequest['ClientMatter']);
  }

  private saveClinetMatter1(e): void {
    this.editedRequest.ClientMatter = e.fldClientMatter;
    //console.log(this.editedRequest.ClientMatter);
  }


  private updatedGuestDetailsValid(): boolean {
    if (this.editedRequest['ReasonCode'] == null || this.editedRequest['ReasonCode'] == '') {
      return false;
    }
    if (this.editedRequest['AssociatedTicketDeliveryType'] == null ||
      this.editedRequest['AssociatedTicketDeliveryType'] === '') {
      return false;
    }
    if (!this.editedRequest['formctrl_guestName'].valid) {
      return false;
    }
    if (!this.editedRequest['formctrl_guestEmail'].valid) {
      return false;
    }
    if (!this.editedRequest['formctrl_guestCompany'].valid) {
      return false;
    }
    if (!this.editedRequest['formctrl_guestNotes'].valid) {
      return false;
    }
    if(this.editedRequest['ReasonCode'] != null && this.editedRequest['ReasonCode'] === 'EXISTING CLIENT'){
      if(this.editedRequest['ClientMatter'] == null || this.editedRequest['ClientMatter'] == '' || typeof this.editedRequest['ClientMatter'] === 'string'){
        this.editedRequest['ClientMatter'] = '';
        return false;
      }
    }
    return true;
  }

  private isInPast(eventDateTime): boolean {
    if (new Date(eventDateTime) > new Date()) {
      return false;
    }
    return true;
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
