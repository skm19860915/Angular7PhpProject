import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { FormControl, Validators } from '@angular/forms';
import { SpecialRequestService } from '../services/special-request.service';
import { SpecialRequest } from '../services/observables/specialRequest';
import { SpecialRequestReply } from '../services/observables/specialRequestReply';
import { UserService } from '../services/user.service';
import { LoaderService } from '../services/loader/loader.service';

// Dirty workaround
// See: https://github.com/t4t5/sweetalert/issues/799
import * as _swal from 'sweetalert';
import { SweetAlert } from 'sweetalert/typings/core';
import { NgbModalRef, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute } from '@angular/router';
const swal: SweetAlert = _swal as any;

@Component({
  selector: 'app-special-requests',
  templateUrl: './special-requests.component.html',
  styleUrls: ['./special-requests.component.css']
})
export class SpecialRequestsComponent implements OnInit {

  @ViewChild('addSpecialRequestModalObj') addReqModalRef: ElementRef;

  newSpecialRequest;
  newRequestReply;
  selectedRequestIndex = -1;
  mobileSelectedRequestIndex = -1;
  addSpecialRequestModal: NgbModalRef;
  addRequestReplyModal: NgbModalRef;
  requestDetailsModal: NgbModalRef;
  pageNbr: number = 1;

  constructor(
    public specialRequestService: SpecialRequestService,
    public userService: UserService,
    private modalService: NgbModal,
    private loader: LoaderService,
    private route: ActivatedRoute) { }

  ngOnInit() {
    this.getTicketRequests();
  }

  ngAfterViewInit() {
    // Subscribe to activated route to catch add request
    // event from overview page
    setTimeout(() => {
      this.route.queryParams.subscribe(params => {
        if(params['action'] && params['action'] === 'add') {
          this.initNewSpecialRequest(params['title']);
          this.addSpecialRequestModal = this.modalService.open(this.addReqModalRef);
        }
      });
    });
  }

  openModal(modal, content) {
    switch(modal) {
      case "addSpecialRequestModal":
        this.addSpecialRequestModal = this.modalService.open(content);
        this.addSpecialRequestModal.result.then(() => {
          //console.log("addSpecialRequestModal closed");
        }, () => {
          //console.log("guestDetails closed");
        });
        break;
      case "addRequestReplyModal":
        this.addRequestReplyModal = this.modalService.open(content);
        this.addRequestReplyModal.result.then(() => {
          //console.log("addRequestReplyModal closed");
        }, () => {
          //console.log("addRequestReplyModal closed");
        });
        break;
      case "requestDetailsModal":
        this.requestDetailsModal = this.modalService.open(content, { size: "lg", backdrop: "static" });
        this.requestDetailsModal.result.then(() => {
          //console.log("details Modal closed");
          this.selectedRequestIndex = -1;
        }, () => {
          //console.log("details Modal closed");
          this.selectedRequestIndex = -1;
        });
        break;
    }
  }

  initNewSpecialRequest(title?) {

    this.newSpecialRequest = new SpecialRequest;
    this.newSpecialRequest.formctrl_title = new FormControl('', Validators.required);
    this.newSpecialRequest.formctrl_description = new FormControl('', Validators.required);
    this.newSpecialRequest.RequestedBy = this.userService.selUser.userAccount;
    this.newSpecialRequest.CreatedBy = this.userService.curUser.userAccount;

    // Autofill from URL
    if(title) {
      this.newSpecialRequest.RequestTitle = title;
      this.newSpecialRequest.formctrl_title.setValue(title);
    }
  }

  getRequestIndex(requestNbr) {
    return this.specialRequestService.specialTicketRequests.findIndex(r => r.SpecialRequestNumber === requestNbr);
  }

  initNewRequestReply(i, mobile) {
    let requestIndex = i;
    if (!mobile)
      requestIndex = this.getRequestIndex(i);
    this.newRequestReply = new SpecialRequestReply;
    this.newRequestReply.SpecialRequestNumber = this.specialRequestService.specialTicketRequests[requestIndex].SpecialRequestNumber;
    this.newRequestReply.formctrl_replyText = new FormControl('', Validators.required);
    this.newRequestReply.ReplyBy = this.userService.selUser.userAccount;
    this.newRequestReply.ReplyCreatedBy = this.userService.curUser.userAccount;
  }

  private addNewRequestReply(): void {
    if(this.newRequestReplyFormValid()) {

      // Copy form control val to new reply object
      this.newRequestReply.ReplyText = this.newRequestReply.formctrl_replyText.value;

      this.specialRequestService.addNewRequestReply(this.newRequestReply).subscribe(
        () => {
          // We already changed the view by manipulating the array, so it's okay to make this async
          this.getTicketRequests();
          this.addRequestReplyModal.close();
          swal("Success!", "The reply was submitted successfully.", "success");
          this.loader.hide();
        },
        err => {
          swal("Error!", "An error occured while submitting the reply.", "error");
          console.log(err);
          this.loader.hide();
        }
      );
    }
    else {
      swal("Error!", "Reply details are invalid.", "error");
    }
  }

  private addNewSpecialRequest(): void {
    if(this.newSpecialRequestFormValid()) {

      // Copy form control val to new reply object
      this.newSpecialRequest.RequestTitle = this.newSpecialRequest.formctrl_title.value;
      this.newSpecialRequest.RequestDescription = this.newSpecialRequest.formctrl_description.value;

      this.specialRequestService.addNewSpecialRequest(this.newSpecialRequest).subscribe(
        () => {
          // We already changed the view by manipulating the array, so it's okay to make this async
          this.getTicketRequests();
          this.addSpecialRequestModal.close();
          swal("Success!", "The special ticket request was submitted successfully.", "success");
          this.loader.hide();
        },
        err => {
          swal("Error!", "An error occured while submitting the special ticket request.", "error");
          console.log(err);
          this.loader.hide();
        }
      );
    }
    else {
      swal("Error!", "Special ticket request details are invalid.", "error");
    }
  }

  private resetRequestSelection() {
    this.mobileSelectedRequestIndex = -1;
    this.selectedRequestIndex = -1;
  }

  private getTicketRequests(): void {
    this.resetRequestSelection();
    if(this.specialRequestService.specialTicketRequests.length>0) {
      this.specialRequestService.specialTicketRequests = [];
    }
    this.specialRequestService.getSpecialTicketRequests();
  }

  private cancelRequest(i, mobile): void {
    if (!mobile)
      i = this.getRequestIndex(i);
    swal({
      title: "You're about to cancel the following special ticket request:",
      text: "\nTitle: " + this.specialRequestService.specialTicketRequests[i].RequestTitle
            + "\nFor User: " + this.specialRequestService.specialTicketRequests[i].RequestedBy,
      icon: "warning",
      buttons: [true, true],
      dangerMode: true
    })
    .then((willCancel) => {
      if (willCancel) {
        this.specialRequestService.cancelSpecialTicketRequest(i).subscribe(
          () => {
            this.getTicketRequests();
            swal("Success!", "Special ticket request was successfully canceled.", "success");
            this.loader.hide();
          },
          err => {
            swal("Error!", "An error occured while canceling the special ticket request.", "error");
            console.error(err);
            this.loader.hide();
          }
        );
      }
    });
  }

  private selectRequest(specialRequestNbr) {
    const i = this.getRequestIndex(specialRequestNbr);
    if(this.specialRequestService.specialTicketRequests[this.mobileSelectedRequestIndex])
      this.specialRequestService.specialTicketRequests[this.mobileSelectedRequestIndex].selected = false;

    if(this.mobileSelectedRequestIndex != i) {
      this.mobileSelectedRequestIndex = i;
      this.specialRequestService.specialTicketRequests[i].selected = true;
    }
    else
      this.mobileSelectedRequestIndex = -1;

    this.selectedRequestIndex = i;
  }

  private newSpecialRequestFormValid(): boolean {
    if(!this.newSpecialRequest.formctrl_title.valid) {
      return false;
    }
    if(!this.newSpecialRequest.formctrl_description.valid) {
      return false;
    }
    return true;
  }

  private newRequestReplyFormValid(): boolean {
    if(!this.newRequestReply.formctrl_replyText.valid) {
      return false;
    }
    return true;
  }
}
