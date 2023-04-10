import { Component, OnInit } from '@angular/core';
import { UserService } from '../../services/user.service';
import { TicketService } from '../../services/ticket.service';
import { DatePipe } from '@angular/common';
import { LoaderService } from '../../services/loader/loader.service';
import { SpecialRequestService } from '../../services/special-request.service';
import { NgbModalRef, NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-maintain-special-requests',
  templateUrl: './admin-maintain-special-requests.component.html',
  styleUrls: ['./admin-maintain-special-requests.component.css']
})
export class AdminMaintainSpecialRequestsComponent implements OnInit {

  public actionList = [
    {
      actionIcon: 'fa-check',
      actionText: 'Approve selection'
    },
    {
      actionIcon: 'fa-thumbs-down',
      actionText: 'Deny selection'
    },
    {
      actionIcon: 'fa-times',
      actionText: 'Cancel selection'
    }
  ];

  selectedRequests = [];
  selectedRequestIndex = -1;
  filterByUser;
  filterStatus = 'SUBMITTED';
  pageNbr: number = 1;
  actionSelectAll = false;
  requestDetailsModal: NgbModalRef;

  constructor(
    public requestService: SpecialRequestService,
    public userService: UserService,
    public ticketService: TicketService,
    private datePipe: DatePipe,
    private loader: LoaderService,
    private modalService: NgbModal
  ) { }

  ngOnInit() {
    this.getRequests();
  }

  openModal(modal, content) {
    switch(modal) {
      case "requestDetailsModal":
        this.requestDetailsModal = this.modalService.open(content, { size: "lg", backdrop: "static" });
        this.requestDetailsModal.result.then(() => { 
          this.selectedRequestIndex = -1;
        }, () => { 
          this.selectedRequestIndex = -1;
        });
        break;
    }
  }

  private getRequests() {
    this.requestService.getSpecialTicketRequests();
  }

  clearSelection() {
    this.selectedRequests = [];
    this.actionSelectAll = false;
  }

  selectAll() {
    this.actionSelectAll = !this.actionSelectAll;
    for (let i = 0; i < this.requestService.specialTicketRequests.length; i++) {
      const request = this.requestService.specialTicketRequests[i];
      let hidden = false;
      if (this.filterByUser && request.RequestedBy !== this.filterByUser)
        hidden = true;
      if (this.filterStatus && request.Status !== this.filterStatus)
        hidden = true;
      if (!hidden) {
        if (!this.selectedRequests.includes(request.SpecialRequestNumber) && this.actionSelectAll) {
          this.selectedRequests.push(request.SpecialRequestNumber);
        }
        else if (this.selectedRequests.includes(request.SpecialRequestNumber) && !this.actionSelectAll) {
          this.selectedRequests.splice(this.selectedRequests.indexOf(request.SpecialRequestNumber), 1);
        }
      }
    }
  }

  selectRequest(requestNumber) {
    if (this.selectedRequests.includes(requestNumber))
      this.selectedRequests.splice(this.selectedRequests.indexOf(requestNumber), 1);
    else
      this.selectedRequests.push(requestNumber);

    this.selectedRequestIndex = this.getRequestIndex(requestNumber);
  }

  getRequestIndex(requestNbr) {
    return this.requestService.specialTicketRequests.findIndex(r => r.SpecialRequestNumber === requestNbr);
  }

  private executeAction(action) {
    switch (action) {
      case this.actionList[0]:
        this.approveSelection();
        break;
      case this.actionList[1]:
        this.denySelection();
        break;
      case this.actionList[2]:
        this.cancelSelection();
        break;
    }
  }

  public statusFilterUpdate(filterVal) {
    this.filterStatus = filterVal;
  }

  public requestedByFilterUpdate(filterVal) {
    this.filterByUser = filterVal;
  }

  private buildModalRequestList(action) {
    let requestsForModal = '';
    for (let reqNbr of this.selectedRequests) {
      const i = this.requestService.specialTicketRequests.findIndex(r => r.SpecialRequestNumber === reqNbr);
      if (action == 'approve' && this.requestService.specialTicketRequests[i].Status != 'APPROVED' && this.requestService.specialTicketRequests[i].Status != 'CANCELLED' ||
        action == 'deny' && this.requestService.specialTicketRequests[i].Status != 'NOT APPROVED' && this.requestService.specialTicketRequests[i].Status != 'CANCELLED' ||
        action == 'cancel' && this.requestService.specialTicketRequests[i].Status != 'CANCELLED') {
        requestsForModal += "Title: \"" + this.requestService.specialTicketRequests[i].RequestTitle + "\"";
        requestsForModal += " | Requested By: ";
        requestsForModal += this.requestService.specialTicketRequests[i].RequestedBy;
        requestsForModal += "\n";
      }
    }
    return requestsForModal;
  }

  denySelection() {
    let requestsForModal = '';
    requestsForModal = this.buildModalRequestList('deny');
    if (requestsForModal.length > 0) {
      swal({
        title: "You are about to deny the following ticket requests:",
        text: requestsForModal,
        icon: "warning",
        buttons: [true, true],
        dangerMode: true
      })
        .then((willApprove) => {
          if (willApprove) {
            let errors = false;
            for (let selReqIdx = 0; selReqIdx < this.selectedRequests.length; selReqIdx++) {
              const i = this.requestService.specialTicketRequests.findIndex(r => r.SpecialRequestNumber === this.selectedRequests[selReqIdx]);
              if (this.requestService.specialTicketRequests[i].Status != 'CANCELLED' && this.requestService.specialTicketRequests[i].Status != 'NOT APPROVED') {
                this.requestService.specialTicketRequests[i].Status = 'NOT APPROVED';
                this.requestService.denySpecialTicketRequest(i).subscribe(
                  data => {
                    // Update ticket status in addition
                    this.ticketService.updateTicketStatus(this.requestService.specialTicketRequests[i].AssociatedTicketRecordNumber, 'AVAILABLE').subscribe(
                      data => {
                        if (selReqIdx == this.selectedRequests.length - 1) { //LAST
                          this.clearSelection();
                          this.getRequests();
                          swal("Success!", "Ticket request(s) denial was successful.", "success");
                        }
                        this.loader.hide();
                      },
                      err => {
                        if (selReqIdx == this.selectedRequests.length - 1) { //LAST
                          this.showServiceErrorMsg(err);
                        }
                        this.loader.hide();
                      }
                    )
                  },
                  err => {
                    if (selReqIdx == this.selectedRequests.length - 1) { //LAST
                      this.showServiceErrorMsg(err);
                    }
                  }
                );
              }
            }
          }
        });
    }
    else
      swal("Error!", "None of the selected tickets can be denied.", "error");
  }

  cancelSelection() {
    let requestsForModal = '';
    requestsForModal = this.buildModalRequestList('cancel');
    if (requestsForModal.length > 0) {
      swal({
        title: "You are about to cancel the following special ticket requests:",
        text: requestsForModal,
        icon: "warning",
        buttons: [true, true],
        dangerMode: true
      })
      .then((willApprove) => {
        if (willApprove) {
          let errors = false;
          for (let selReqIdx = 0; selReqIdx < this.selectedRequests.length; selReqIdx++) {
            const i = this.requestService.specialTicketRequests.findIndex(r => r.SpecialRequestNumber === this.selectedRequests[selReqIdx]);
            if (this.requestService.specialTicketRequests[i].Status != 'CANCELLED') {
              this.requestService.cancelSpecialTicketRequest(i).subscribe(
                () => {
                  if (selReqIdx == this.selectedRequests.length - 1) { //LAST
                    this.clearSelection();
                    this.getRequests();
                    swal("Success!", "Special ticket request(s) cancellation was successful.", "success");
                  }
                  this.loader.hide();
                },
                err => {
                  if (selReqIdx == this.selectedRequests.length - 1) { //LAST
                    this.showServiceErrorMsg(err);
                  }
                  this.loader.hide();
                }
              );
            }
          }
        }
      });
    }
    else
      swal("Error!", "None of the selected ticket request(s) can be cancelled.", "error");
  }

  approveSelection() {
    let requestsForModal = '';
    requestsForModal = this.buildModalRequestList('approve');
    if (requestsForModal.length > 0) {
      swal({
        title: "You are about to approve the following ticket requests:",
        text: this.buildModalRequestList('approve'),
        icon: "warning",
        buttons: [true, true],
        dangerMode: true
      })
      .then((willApprove) => {
        if (willApprove) {
          for (let selReqIdx = 0; selReqIdx < this.selectedRequests.length; selReqIdx++) {
            const i = this.requestService.specialTicketRequests.findIndex(r => r.SpecialRequestNumber === this.selectedRequests[selReqIdx]);
            if (this.requestService.specialTicketRequests[i].Status != 'CANCELLED' && this.requestService.specialTicketRequests[i].Status != 'APPROVED') {
              this.requestService.approveSpecialTicketRequest(i).subscribe(
                () => {
                  if (selReqIdx == this.selectedRequests.length - 1) { //LAST
                    this.clearSelection();
                    this.getRequests();
                    swal("Success!", "Ticket request(s) approval was successful.", "success");
                  }
                  this.loader.hide();
                },
                err => {
                  if (selReqIdx == this.selectedRequests.length - 1) { //LAST
                    this.showServiceErrorMsg(err);
                  }
                  this.loader.hide();
                }
              );
            }
          }
        }
      });
    }
    else {
      swal("Error!", "None of the selected tickets can be approved.", "error");
    }
  }

  private showServiceErrorMsg(err) {
    swal("Error!", "An error occured while approving the selected ticket request(s).", "error");
    this.clearSelection();
  }
}