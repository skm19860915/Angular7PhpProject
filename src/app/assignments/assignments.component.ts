import { Component, OnInit } from '@angular/core';
import { FormControl, Validators } from '@angular/forms';
import { UserService } from '../services/user.service';
import { AssignmentService } from '../services/assignment.service';
import { TicketService } from '../services/ticket.service';
import { DatePipe } from '@angular/common';
import { LoaderService } from '../services/loader/loader.service';

// Dirty workaround
// See: https://github.com/t4t5/sweetalert/issues/799
import * as _swal from 'sweetalert';
import { SweetAlert } from 'sweetalert/typings/core';
import { NgbModalRef, NgbModal, NgbDropdown } from '@ng-bootstrap/ng-bootstrap';
import { VenueService } from '../services/venue.service';
const swal: SweetAlert = _swal as any;

@Component({
  selector: 'app-assignments',
  templateUrl: './assignments.component.html',
  styleUrls: ['./assignments.component.css']
})
export class AssignmentsComponent implements OnInit {

  mobileSelectedAssignmentIndex = -1;
  editedAssignment;
  transferAssignment;
  transferModal: NgbModalRef;
  editAssignmentModal: NgbModalRef;
  assignmentInfoModal: NgbModalRef;
  pageNbr = 1;

  constructor(
    public assignmentService: AssignmentService,
    public userService: UserService,
    public ticketService: TicketService,
    public venueService: VenueService,
    private datePipe: DatePipe,
    private modalService: NgbModal,
    private loader: LoaderService
  ) { }

  ngOnInit() {
    this.venueService.getAllEventVenues();
    this.getTicketAssignments();
  }

  openModal(modal, content) {
    switch (modal) {
      case 'editAssignmentModal':
        this.editAssignmentModal = this.modalService.open(content, { backdrop: 'static' });
        this.editAssignmentModal.result.then(() => {
          // console.log("guestDetails closed");
          this.editedAssignment = null;
        }, () => {
          // console.log("guestDetails closed");
          this.editedAssignment = null;
        });
        break;
      case 'transferModal':
        this.transferModal = this.modalService.open(content, { backdrop: 'static' });
        this.transferModal.result.then(() => {
          // console.log("transferModal closed");
          this.transferAssignment = null;
        }, () => {
          // console.log("transferModal closed");
          this.transferAssignment = null;
        });
        break;
      case 'assignmentInfoModal':
        this.assignmentInfoModal = this.modalService.open(content, { backdrop: 'static' });
        this.assignmentInfoModal.result.then(() => {
          // console.log("infoModal closed");
        }, () => {
          // console.log("infoModal closed");
        });
        break;
    }
  }

  private getTicketAssignments(): void {
    if (this.assignmentService.ticketAssignments.length > 0) {
      this.assignmentService.ticketAssignments = [];
    }
    this.assignmentService.getTicketAssignments();
  }

  getAssignmentIndex(assignmentNbr) {
    return this.assignmentService.ticketAssignments.findIndex(a => a.AssignmentNumber === assignmentNbr);
  }

  private selectAssignment(assignmentNbr) {
    const i = this.getAssignmentIndex(assignmentNbr);
    if (!this.isInPast(this.assignmentService.ticketAssignments[i].EventDateTime.date.replace(' ', 'T'))
      && this.assignmentService.ticketAssignments[i].Status != 'CANCELLED') {
      if (this.assignmentService.ticketAssignments[this.mobileSelectedAssignmentIndex]) {
        this.assignmentService.ticketAssignments[this.mobileSelectedAssignmentIndex].selected = false;
      }
      if (this.mobileSelectedAssignmentIndex != i) {
        this.mobileSelectedAssignmentIndex = i;
        this.assignmentService.ticketAssignments[i].selected = true;
      } else {
        this.mobileSelectedAssignmentIndex = -1;
      }
    }
  }

  private cancelAssignment(idx, mobile) {
    let i = this.mobileSelectedAssignmentIndex;
    if (!mobile) {
      i = this.getAssignmentIndex(idx);
    }

    swal({
      title: 'You are about to cancel the following ticket assignment:',
      text: '\n' + this.assignmentService.ticketAssignments[i].EventDescription
        + '\n' + this.datePipe.transform(this.assignmentService.ticketAssignments[i].EventDateTime.date.replace(' ', 'T').split('.')[0], 'EEEE, M/d/y @ h:mm a')
        + '\nSection: ' + this.assignmentService.ticketAssignments[i].Section
        + ' | Row: ' + this.assignmentService.ticketAssignments[i].Row
        + ' | Seat: ' + this.assignmentService.ticketAssignments[i].Seat
        + '\nFor User: ' + this.assignmentService.ticketAssignments[i].RequestedBy,
      icon: 'warning',
      buttons: [true, true],
      dangerMode: true
    })
      .then((willCancel) => {
        if (willCancel) {
          this.assignmentService.cancelTicketAssignment(i).subscribe(
            () => {
              this.mobileSelectedAssignmentIndex = -1;
              this.getTicketAssignments();
              swal('Success!', 'Assignment was successfully canceled.', 'success');
              this.loader.hide();
            },
            err => {
              this.mobileSelectedAssignmentIndex = -1;
              swal('Error!', 'An error occured while canceling the ticket assignment.', 'error');
              console.error(err);
              this.loader.hide();
            }
          );
        }
      });
  }

  private editAssignment(i, mobile) {

    let assignmentIndex = i;

    if (!mobile) {
      assignmentIndex = this.getAssignmentIndex(i);
    }

    // Save Copying
    this.editedAssignment = JSON.parse(JSON.stringify(this.assignmentService.ticketAssignments[assignmentIndex]));

    this.editedAssignment['formctrl_guestName'] = new FormControl('', Validators.required);
    this.editedAssignment['formctrl_guestName'].setValue(this.editedAssignment.GuestName);

    this.editedAssignment['formctrl_guestEmail'] = new FormControl();
    this.editedAssignment['formctrl_guestEmail'].setValue(this.editedAssignment.GuestEmail);

    this.editedAssignment['formctrl_guestCompany'] = new FormControl('', Validators.required);
    this.editedAssignment['formctrl_guestCompany'].setValue(this.editedAssignment.GuestCompany);

    this.editedAssignment['formctrl_guestNotes'] = new FormControl();
    this.editedAssignment['formctrl_guestNotes'].setValue(this.editedAssignment.AssignmentNotes);
  }

  private selectAssignmentForTransfer(assignment, mobile) {
    if (mobile) {
      this.transferAssignment = this.assignmentService.ticketAssignments[assignment];
      return;
    }
    this.transferAssignment = assignment;
  }

  private transferTicketAssignment() {
    this.assignmentService.transferTicketAssignment(this.transferAssignment).subscribe(
      () => {
        this.transferAssignment = null;
        this.mobileSelectedAssignmentIndex = -1;
        this.getTicketAssignments();
        this.transferModal.close();
        swal('Success!', 'The ticket assignment was successfully transfered.', { icon: 'success' });
        this.loader.hide();
      },
      err => {
        swal('Error!', 'An error occured while transfering the ticket assignment.', 'error');
        console.error(err);
        this.loader.hide();
      }
    );
  }

  public transferPreSelection(newUser) {
    if (this.transferAssignment.RequestedBy != newUser.userAccount) {
      this.transferAssignment.TransferToUser = newUser;
    } else {
      swal('Error!', 'You can\'t transfer this assignment to the same user.', 'error');
    }
  }

  private updateAssignment() {
    if (!this.updatedGuestDetailsValid()) {
      swal('Error!', 'Guest details are invalid.', 'error');
    } else {

      // Copy from ctrl values to edited assignment obj
      this.editedAssignment.GuestName = this.editedAssignment.formctrl_guestName.value;
      this.editedAssignment.GuestEmail = this.editedAssignment.formctrl_guestEmail.value;
      this.editedAssignment.GuestCompany = this.editedAssignment.formctrl_guestCompany.value;
      this.editedAssignment.AssignmentNotes = this.editedAssignment.formctrl_guestNotes.value;

      this.assignmentService.updateTicketAssignment(this.editedAssignment).subscribe(
        () => {
          this.editedAssignment = null;
          this.mobileSelectedAssignmentIndex = -1;
          this.getTicketAssignments();
          this.editAssignmentModal.close();
          swal('Success!', 'The ticket assignment was successfully updated.', 'success');
          this.loader.hide();
        },
        err => {
          swal('Error!', 'An error occured while updating the ticket assignment.', 'error');
          console.error(err);
          this.loader.hide();
        }
      );
    }
  }

  private updatedGuestDetailsValid(): boolean {

    if (this.editedAssignment['AssociatedReasonCode'] == null) {
      return false;
    }
    if (!this.editedAssignment['formctrl_guestName'].valid) {
      return false;
    }
    if (!this.editedAssignment['formctrl_guestEmail'].valid) {
      return false;
    }
    if (!this.editedAssignment['formctrl_guestCompany'].valid) {
      return false;
    }
    return true;
  }

  private isInPast(eventDateTime): boolean {
    if (new Date(eventDateTime) > new Date()) {
      return false;
    }
    return true;
  }
}
