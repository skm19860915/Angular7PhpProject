import { Component, OnInit } from '@angular/core';
import { RequestService } from '../../services/request.service';
import { UserService } from '../../services/user.service';
import { TicketService } from '../../services/ticket.service';
import { DatePipe } from '@angular/common';
import { NgbModalRef, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { LoaderService } from '../../services/loader/loader.service';

@Component({
  selector: 'app-admin-maintain-requests',
  templateUrl: './admin-maintain-requests.component.html',
  styleUrls: ['./admin-maintain-requests.component.css']
})
export class AdminMaintainRequestsComponent implements OnInit {

  public actionList = [
    {
      actionIcon: 'fa-check',
      actionText: 'Approve selection'
    },
    {
      actionIcon: 'fa-thumbs-down',
      actionText: 'Decline selection'
    },
    {
      actionIcon: 'fa-times',
      actionText: 'Cancel selection'
    }
  ];

  selectedRequests = [];
  filterByUser;
  selRequestForDetailsIndex: number = -1;
  requestDetailsModal: NgbModalRef;
  filterStatus = 'SUBMITTED/PENDING';
  pageNbr: number = 1;
  actionSelectAll = false;

  constructor(
    public requestService: RequestService,
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
        this.requestDetailsModal = this.modalService.open(content, { backdrop: "static" });
        this.requestDetailsModal.result.then(() => {
          this.selRequestForDetailsIndex = -1;
        }, () => {
          this.selRequestForDetailsIndex = -1;
        });
        break;
    }
  }

  private getRequests() {
    this.requestService.getTicketRequests();
  }

  clearSelection() {
    this.selectedRequests = [];
    this.actionSelectAll = false;
  }

  selectAll() {
    this.actionSelectAll = !this.actionSelectAll;
    for (let i = 0; i < this.requestService.ticketRequests.length; i++) {
      const request = this.requestService.ticketRequests[i];
      let hidden = false;
      if (this.filterByUser && request.RequestedBy !== this.filterByUser)
        hidden = true;
      if (this.filterStatus && request.statusOfTicketRequest !== this.filterStatus)
        hidden = true;
      if (!hidden) {
        if (!this.selectedRequests.includes(request.RequestNumber) && this.actionSelectAll) {
          this.selectedRequests.push(request.RequestNumber);
        }
        else if (this.selectedRequests.includes(request.RequestNumber) && !this.actionSelectAll) {
          this.selectedRequests.splice(this.selectedRequests.indexOf(request.RequestNumber), 1);
        }
      }
    }
  }

  selectRequest(requestNumber) {
    if (this.selectedRequests.includes(requestNumber))
      this.selectedRequests.splice(this.selectedRequests.indexOf(requestNumber), 1);
    else
      this.selectedRequests.push(requestNumber);
  }

  selectRequestForDetails(requestNumber) {
    this.selRequestForDetailsIndex = this.requestService.ticketRequests.findIndex(
      a => a.RequestNumber === requestNumber
    );
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
      const i = this.requestService.ticketRequests.findIndex(r => r.RequestNumber === reqNbr);
      if (action == 'approve' && this.requestService.ticketRequests[i].statusOfTicketRequest != 'APPROVED' && this.requestService.ticketRequests[i].statusOfTicketRequest != 'CANCELLED' ||
        action == 'deny' && this.requestService.ticketRequests[i].statusOfTicketRequest != 'NOT APPROVED' && this.requestService.ticketRequests[i].statusOfTicketRequest != 'CANCELLED' ||
        action == 'cancel' && this.requestService.ticketRequests[i].statusOfTicketRequest != 'CANCELLED') {
        requestsForModal += "#";
        requestsForModal += this.requestService.ticketRequests[i].RequestNumber;
        requestsForModal += " | ";
        requestsForModal += this.requestService.ticketRequests[i].RequestedBy;
        requestsForModal += " | ";
        requestsForModal += this.requestService.ticketRequests[i].EventDescription;
        requestsForModal += " | ";
        requestsForModal += this.datePipe.transform(this.requestService.ticketRequests[i].EventDateTime.date.replace(" ", "T").split(".")[0], 'EEEE, M/d/y @ h:mm a');
        requestsForModal += "\n";
      }
    }
    return requestsForModal;
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
            let errors = false;
            for (let selReqIdx = 0; selReqIdx < this.selectedRequests.length; selReqIdx++) {
              const i = this.requestService.ticketRequests.findIndex(r => r.RequestNumber === this.selectedRequests[selReqIdx]);
              if (this.requestService.ticketRequests[i].statusOfTicketRequest != 'CANCELLED' && this.requestService.ticketRequests[i].statusOfTicketRequest != 'APPROVED') {
                this.requestService.ticketRequests[i].statusOfTicketRequest = 'APPROVED';
                this.requestService.approveTicketRequest(i).subscribe(
                  () => {
                    // Update ticket status in addition
                    this.ticketService.updateTicketStatus(this.requestService.ticketRequests[i].AssociatedTicketRecordNumber, 'ASSIGNED').subscribe(
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
      swal("Error!", "None of the selected tickets can be approved.", "error");
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
              const i = this.requestService.ticketRequests.findIndex(r => r.RequestNumber === this.selectedRequests[selReqIdx]);
              if (this.requestService.ticketRequests[i].statusOfTicketRequest != 'CANCELLED' && this.requestService.ticketRequests[i].statusOfTicketRequest != 'NOT APPROVED') {
                this.requestService.ticketRequests[i].statusOfTicketRequest = 'NOT APPROVED';
                this.requestService.declineTicketRequest(i).subscribe(
                  data => {
                    // Update ticket status in addition
                    this.ticketService.updateTicketStatus(this.requestService.ticketRequests[i].AssociatedTicketRecordNumber, 'AVAILABLE').subscribe(
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
        title: "You are about to cancel the following ticket requests:",
        text: requestsForModal,
        icon: "warning",
        buttons: [true, true],
        dangerMode: true
      })
        .then((willApprove) => {
          if (willApprove) {
            let errors = false;
            for (let selReqIdx = 0; selReqIdx < this.selectedRequests.length; selReqIdx++) {
              const i = this.requestService.ticketRequests.findIndex(r => r.RequestNumber === this.selectedRequests[selReqIdx]);
              if (this.requestService.ticketRequests[i].statusOfTicketRequest != 'CANCELLED') {
                this.requestService.cancelTicketRequest(i).subscribe(
                  data => {
                    if (selReqIdx == this.selectedRequests.length - 1) { //LAST
                      this.clearSelection();
                      this.getRequests();
                      swal("Success!", "Ticket request(s) cancellation was successful.", "success");
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

  private showServiceErrorMsg(err) {
    console.error(err);
    swal("Error!", "An error occured while updating the selected ticket request(s).", "error");
    this.clearSelection();
  }
}
