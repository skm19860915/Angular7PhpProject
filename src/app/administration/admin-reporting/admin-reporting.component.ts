import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { AssignmentService } from '../../services/assignment.service';
import { UserService } from '../../services/user.service';
import { DatePipe } from '@angular/common';
import { NgbModalRef, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { LoaderService } from '../../services/loader/loader.service';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { TicketService } from '../../services/ticket.service';

@Component({
  selector: 'app-admin-reporting',
  templateUrl: './admin-reporting.component.html',
  styleUrls: ['./admin-reporting.component.css']
})
export class AdminReportingComponent implements OnInit {

  selectedAssignments = [];
  selAssignmentForDetailsIndex = -1;
  filterAssignedToByUser;
  filterStatus = 'AWAITING ADMIN';
  pageNbr: number = 1;
  actionSelectAll = false;
  transferUserSelected: boolean = false;

  visibleFilterForm: boolean = false;
  formgrp_fromDate = null;
  formgrp_toDate = null;
  fromDate: string = '';
  toDate: string = '';
  reasonCode: string = '';

  constructor(
    public assignmentService: AssignmentService,
    public userService: UserService,
    private datePipe: DatePipe,
    private modalService: NgbModal,
    private loader: LoaderService,
    public ticketService: TicketService
  ) { }

  ngOnInit() {
    this.visibleFilterForm = true;
    this.formgrp_fromDate = new FormGroup({formctrl_fromDate: new FormControl('', Validators.required)});
    this.formgrp_toDate = new FormGroup({formctrl_toDate: new FormControl('', Validators.required)});
    this.ticketService.getUsageReasons();
  }

  getReportData(){
    let fromDate = this.formgrp_fromDate.controls.formctrl_fromDate;
    let toDate = this.formgrp_toDate.controls.formctrl_toDate;

    if (fromDate.valid && toDate.valid){
      this.visibleFilterForm = false;
      this.getAssignments(fromDate.value, toDate.value, this.reasonCode);
    }
  }

  public statusFilterUpdate(filterVal) {
    this.filterStatus = filterVal;
  }

  public assignedToByUserFilterUpdate(filterVal) {
    this.filterAssignedToByUser = filterVal;
  }

  private getAssignments(fromDate, toDate, reasonCode) {
    this.assignmentService.getTicketAssignmentsForReporting(fromDate, toDate, reasonCode);
    this.fromDate = (new Date(fromDate).getMonth() + 1).toString() + "/" +  new Date(fromDate).getDate().toString() + "/" + new Date(fromDate).getFullYear().toString();
    this.toDate = (new Date(toDate).getMonth() + 1).toString() + "/" +  new Date(toDate).getDate().toString() + "/" + new Date(toDate).getFullYear().toString();
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

  getAssignmentIndex(assignmentNbr) {
    return this.assignmentService.ticketAssignments.findIndex(a => a.AssignmentNumber === assignmentNbr);
  }
}
