import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { AssignmentService } from '../../services/assignment.service';
import { UserService } from '../../services/user.service';
import { DatePipe } from '@angular/common';
import { NgbModalRef, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { LoaderService } from '../../services/loader/loader.service';

@Component({
  selector: 'app-admin-maintain-assignments',
  templateUrl: './admin-maintain-assignments.component.html',
  styleUrls: ['./admin-maintain-assignments.component.css']
})
export class AdminMaintainAssignmentsComponent implements OnInit {

  public actionList = [
    {
      actionIcon: '',
      actionText: 'Mark as Tickets Ready For Delivery'
    },
    {
      actionIcon: '',
      actionText: 'Mark as Tickets Delivered'
    },
    {
      actionIcon: '',
      actionText: 'Transfer to other user'
    },
    {
      actionIcon: '',
      actionText: 'Cancel'
    }
  ];

  selectedAssignments = [];
  selAssignmentForDetailsIndex = -1;
  filterAssignedToByUser;
  filterStatus = 'AWAITING ADMIN';
  transferModal: NgbModalRef;
  assignmentDetailsModal: NgbModalRef;
  pageNbr: number = 1;
  actionSelectAll = false;
  transferUserSelected: boolean = false;
  @ViewChild('transferModalObj') transferModalRef: ElementRef;

  constructor(
    public assignmentService: AssignmentService,
    public userService: UserService,
    private datePipe: DatePipe,
    private modalService: NgbModal,
    private loader: LoaderService
  ) { }

  ngOnInit() {
    this.getAssignments();
  }

  openModal(modal, content) {
    switch(modal) {
      case "transferModal":
        this.transferModal = this.modalService.open(content, { size: "lg", backdrop: "static" });
        this.transferModal.result.then(() => {
        }, () => {
        });
        break;
      case "assignmentDetailsModal":
        this.assignmentDetailsModal = this.modalService.open(content, { backdrop: "static" });
        this.assignmentDetailsModal.result.then(() => {
          this.selAssignmentForDetailsIndex = -1;
        }, () => {
          this.selAssignmentForDetailsIndex = -1;
        });
        break;
    }
  }

  public statusFilterUpdate(filterVal) {
    this.filterStatus = filterVal;
  }

  public assignedToByUserFilterUpdate(filterVal) {
    this.filterAssignedToByUser = filterVal;
  }

  private getAssignments() {
    this.assignmentService.getTicketAssignments();
  }

  clearSelection() {
    this.selectedAssignments = [];
    this.actionSelectAll = false;
  }

  selectAll() {
    this.actionSelectAll = !this.actionSelectAll;
    for (let i = 0; i < this.assignmentService.ticketAssignments.length; i++) {
      const assignment = this.assignmentService.ticketAssignments[i];
      let hidden = false;
      if (this.filterAssignedToByUser && assignment.GuestAssociatedFirmAttorney !== this.filterAssignedToByUser)
        hidden = true;
      if (this.filterStatus && assignment.Status !== this.filterStatus)
        hidden = true;
      // Cancelled assignments are not selectable, we abuse the hidden field here for that purpose
      if (assignment.Status === 'CANCELLED')
        hidden = true;
      if (!hidden) {
        if (!this.selectedAssignments.includes(assignment.AssignmentNumber) && this.actionSelectAll) {
          this.selectedAssignments.push(assignment.AssignmentNumber);
        }
        else if (this.selectedAssignments.includes(assignment.AssignmentNumber) && !this.actionSelectAll) {
          this.selectedAssignments.splice(this.selectedAssignments.indexOf(assignment.AssignmentNumber), 1);
        }
      }
    }
  }

  selectAssignment(assignmentNumber) {
    const i = this.assignmentService.ticketAssignments.findIndex(a => a.AssignmentNumber === assignmentNumber);
    if (this.assignmentService.ticketAssignments[i].Status != 'CANCELLED') {
      if (this.selectedAssignments.includes(assignmentNumber))
        this.selectedAssignments.splice(this.selectedAssignments.indexOf(assignmentNumber), 1);
      else
        this.selectedAssignments.push(assignmentNumber);
    }
  }

  selectAssignmentForDetails(assignmentNumber) {
    this.selAssignmentForDetailsIndex = this.assignmentService.ticketAssignments.findIndex(
      a => a.AssignmentNumber === assignmentNumber
    );
  }

  transferPreSelection(newUserAccount) {
    for (let selAssIdx = 0; selAssIdx < this.selectedAssignments.length; selAssIdx++) {
      const i = this.assignmentService.ticketAssignments.findIndex(a => a.AssignmentNumber === this.selectedAssignments[selAssIdx]);
      if (this.assignmentService.ticketAssignments[i].RequestedBy != newUserAccount.userAccount) {
        this.assignmentService.ticketAssignments[i].TransferToUser = newUserAccount;
        this.transferUserSelected = true;
      }
      else {
        swal("Error!", "You can't transfer this assignment to the same user.", "error");
        this.transferUserSelected = false;
        return;
      }
    }
  }

  private showServiceErrorMsg(err) {
    console.error(err);
    swal("Error!", "An error occured while approving the selected ticket request(s).", "error");
    // this.clearSelection();
  }

  private buildModalList(action): string {
    let assignmentsForModal = '';
    for (let assignmentNumber of this.selectedAssignments) {
      const i = this.assignmentService.ticketAssignments.findIndex(a => a.AssignmentNumber === assignmentNumber);
      if (action == 'readyForDelivery' && this.assignmentService.ticketAssignments[i].Status != 'TICKETS READY FOR DELIVERY' && this.assignmentService.ticketAssignments[i].Status != 'TICKETS DELIVERED' && this.assignmentService.ticketAssignments[i].Status != 'CANCELLED' ||
        action == 'ticketsDelivered' && this.assignmentService.ticketAssignments[i].Status != 'TICKETS DELIVERED' && this.assignmentService.ticketAssignments[i].Status != 'CANCELLED' ||
        action == 'cancel' && this.assignmentService.ticketAssignments[i].Status != 'CANCELLED') {
        assignmentsForModal += "#";
        assignmentsForModal += this.assignmentService.ticketAssignments[i].AssignmentNumber;
        assignmentsForModal += " | ";
        assignmentsForModal += this.assignmentService.ticketAssignments[i].GuestAssociatedFirmAttorney;
        assignmentsForModal += " | ";
        assignmentsForModal += this.assignmentService.ticketAssignments[i].EventDescription;
        assignmentsForModal += " | ";
        assignmentsForModal += this.datePipe.transform(this.assignmentService.ticketAssignments[i].EventDateTime.date.replace(" ", "T").split(".")[0], 'EEEE, M/d/y @ h:mm a');
        assignmentsForModal += "\n";
      }
    }
    return assignmentsForModal;
  }

  private transfer() {
    for (let selAssIdx = 0; selAssIdx < this.selectedAssignments.length; selAssIdx++) {
      const i = this.assignmentService.ticketAssignments.findIndex(a => a.AssignmentNumber === this.selectedAssignments[selAssIdx]);
      this.assignmentService.transferTicketAssignment(this.assignmentService.ticketAssignments[i]).subscribe(
        () => {
          if (selAssIdx == this.selectedAssignments.length - 1) { // LAST
            this.clearSelection();
            this.selectedAssignments = [];
            this.getAssignments();
            this.transferModal.close();
            swal("Success!", "Ticket transfer was successfully.", { icon: "success" });
          }
          this.loader.hide();
        },
        err => {
          if (selAssIdx == this.selectedAssignments.length - 1) { // LAST
            console.error(err);
            swal("Error!", "An error occured while approving the selected ticket request(s).", "error");
          }
          this.loader.hide();
        }
      );
    }
  }

  private executeAction(action) {
    switch (action) {
      case this.actionList[0]:
        this.markSelectionReadyForDelivery();
        break;
      case this.actionList[1]:
        this.markSelectionTicketsDelivered();
        break;
      case this.actionList[2]:
        this.openModal("transferModal", this.transferModalRef);
        break;
      case this.actionList[3]:
        this.cancelSelection();
        break;
    }
  }

  getAssignmentIndex(assignmentNbr) {
    return this.assignmentService.ticketAssignments.findIndex(a => a.AssignmentNumber === assignmentNbr);
  }

  markSelectionReadyForDelivery() {
    let assignmentsForModal = '';
    assignmentsForModal = this.buildModalList('readyForDelivery');
    if (assignmentsForModal.length > 0) {
      swal({
        title: "You are about to mark the following ticket assignments as 'Tickets Ready For Delivery':",
        text: assignmentsForModal,
        icon: "warning",
        buttons: [true, true],
        dangerMode: true
      })
      .then((willApprove) => {
        if (willApprove) {
          for (let selAssIdx = 0; selAssIdx < this.selectedAssignments.length; selAssIdx++) {
            const i = this.assignmentService.ticketAssignments.findIndex(a => a.AssignmentNumber === this.selectedAssignments[selAssIdx]);
            if (this.assignmentService.ticketAssignments[i].Status != 'CANCELLED'
              && this.assignmentService.ticketAssignments[i].Status != 'TICKETS READY FOR DELIVERY') {
              this.assignmentService.ticketAssignments[i].Status = 'TICKETS READY FOR DELIVERY';
              this.assignmentService.ticketAssignments[i].ActionBy = this.userService.curUser.userAccount;
              this.assignmentService.updateTicketAssignmentStatus(i).subscribe(
                () => {
                  if (selAssIdx == this.selectedAssignments.length - 1) { //LAST
                    this.clearSelection();
                    this.getAssignments();
                    swal("Success!", "Selected ticket assignment(s) are now marked as 'Tickets Ready For Delivery'.", "success");
                  }
                  this.loader.hide();
                },
                err => {
                  if (selAssIdx == this.selectedAssignments.length - 1) { //LAST
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
      swal("Error!", "None of the selected ticket assignments can be marked as 'Tickets Ready For Delivery'.", "error");
  }

  markSelectionTicketsDelivered() {
    let assignmentsForModal = '';
    assignmentsForModal = this.buildModalList('ticketsDelivered');
    if (assignmentsForModal.length > 0) {
      swal({
        title: "You are about to mark the following ticket assignments as 'Tickets Delivered':",
        text: assignmentsForModal,
        icon: "warning",
        buttons: [true, true],
        dangerMode: true
      })
      .then((willApprove) => {
        if (willApprove) {
          for (let selAssIdx = 0; selAssIdx < this.selectedAssignments.length; selAssIdx++) {
            const i = this.assignmentService.ticketAssignments.findIndex(a => a.AssignmentNumber === this.selectedAssignments[selAssIdx]);
            if (this.assignmentService.ticketAssignments[i].Status != 'CANCELLED' && this.assignmentService.ticketAssignments[i].Status != 'TICKETS DELIVERED') {
              this.assignmentService.ticketAssignments[i].Status = 'TICKETS DELIVERED';
              this.assignmentService.ticketAssignments[i].ActionBy = this.userService.curUser.userAccount;
              this.assignmentService.updateTicketAssignmentStatus(i).subscribe(
                () => {
                  if (selAssIdx == this.selectedAssignments.length - 1) { //LAST
                    this.clearSelection();
                    this.getAssignments();
                    swal("Success!", "Selected ticket assignment(s) are now marked as 'Tickets Delivered'.", "success");
                  }
                  this.loader.hide();
                },
                err => {
                  if (selAssIdx == this.selectedAssignments.length - 1) { //LAST
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
      swal("Error!", "None of the selected ticket assignments can be marked as 'Tickets Ready For Delivery'.", "error");
  }

  cancelSelection() {
    let assignmentsForModal = '';
    assignmentsForModal = this.buildModalList('cancel');
    if (assignmentsForModal.length > 0) {
      swal({
        title: "You are about to cancel the following ticket assignment(s):",
        text: assignmentsForModal,
        icon: "warning",
        buttons: [true, true],
        dangerMode: true
      })
        .then((willApprove) => {
          if (willApprove) {
            for (let selAssIdx = 0; selAssIdx < this.selectedAssignments.length; selAssIdx++) {
              const i = this.assignmentService.ticketAssignments.findIndex(a => a.AssignmentNumber === this.selectedAssignments[selAssIdx]);
              this.assignmentService.cancelTicketAssignment(i).subscribe(
                () => {
                  if (selAssIdx == this.selectedAssignments.length - 1) { //LAST
                    this.clearSelection();
                    this.getAssignments();
                    swal("Success!", "Ticket assignment(s) cancellation was successful.", "success");
                  }
                  this.loader.hide();
                },
                err => {
                  if (selAssIdx == this.selectedAssignments.length - 1) { //LAST
                    this.showServiceErrorMsg(err);
                  }
                  this.loader.hide();
                }
              );
            }
          }
        });
    }
    else
      swal("Error!", "None of the selected ticket assignments can be canceled.", "error");
  }
}
