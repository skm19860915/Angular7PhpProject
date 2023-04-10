import { Component, OnInit } from '@angular/core';
import { UserService } from '../../services/user.service';
import { FormControl, Validators } from '@angular/forms';
import { User } from '../../services/observables/user';
import { NgbModalRef, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { LoaderService } from '../../services/loader/loader.service';

@Component({
  selector: 'app-system-maintain-system-users',
  templateUrl: './system-maintain-system-users.component.html',
  styleUrls: ['./system-maintain-system-users.component.css']
})
export class SystemMaintainSystemUsersComponent implements OnInit {

  newUser;
  filterByUsername = "";
  addUserModal: NgbModalRef;
  pageNbr: number = 1;

  constructor(
    public userService: UserService, 
    private modalService: NgbModal,
    private loader: LoaderService
  ) { }

  ngOnInit() {
    this.userService.getAllUsers();
  }

  openModal(content) {
    this.addUserModal = this.modalService.open(content, { backdrop: "static" });
    this.addUserModal.result.then(() => { 
      this.newUser = null;
    }, () => {
      this.newUser = null;
    });
  }

  initNewUserModal() {
    this.newUser = new User;
    this.newUser.formctrl_userName = new FormControl('', Validators.required);
    this.newUser.formctrl_userEmail = new FormControl('', Validators.required);
    this.newUser.userStatus = "ACTIVE";
    this.newUser.memberOfEveryone = 'Y';
    this.newUser.memberOfAssociate = 'N';
    this.newUser.memberOfPartner = 'N';
    this.newUser.memberOfSectionHead = 'N';
    this.newUser.memberOfTicketAdministration = 'N';
    this.newUser.memberOfSystemAdministration = 'N';
  }

  changeCredential(userAccount, credential) {
    swal({
      title: "You are about to set the highest user role for the following user: ",
      text: "\nUser: " + userAccount + "\n" +
            "Highest Role: " + credential + "\n",
      icon: "warning",
      buttons: [true, true],
      dangerMode: true
    })
    .then((willCancel) => {
      if (willCancel) {
        this.updateCredential(userAccount, credential);
        this.userService.updateUser(userAccount).subscribe(
          () => {
            this.userService.getAllUsers(); // We already changed the view by manipulating the array, so it's okay to make this async
            swal("Success!", "User role(s) were successfully changed.", "success");
            this.loader.hide();
          },
          err => {
            swal("Error!", "An error occured while changing the user role(s).", "error");
            console.log(err); 
            this.loader.hide();
          }
        );
      }
    });
  }

  changeStatus(userAccount) {
    const userIndex = this.userService.users.findIndex(u => u.userAccount === userAccount);
    swal({
      title: "You're about to " + this.getPreChangeStatusOppositeWording(this.userService.users[userIndex].userStatus) + " the user account for:",
      text: userAccount,
      icon: "warning",
      buttons: [true, true],
      dangerMode: true
    })
    .then((willCancel) => {
      if (willCancel) {
        if(this.userService.users[userIndex].userStatus == 'ACTIVE')
          this.userService.users[userIndex].userStatus = 'INACTIVE';
        else
          this.userService.users[userIndex].userStatus = 'ACTIVE';

        this.userService.updateUser(userAccount).subscribe(
          () => {
            this.userService.getAllUsers();
            swal(
              "Success!", "User account was successfully " + this.getChangeStatusOppositeWording(this.userService.users[userIndex].userStatus) + ".", "success"
            );
            this.loader.hide();
          },
          err => {
            swal("Error!", "An error occured while changing the user's status.", "error");
            console.log(err); 
            this.loader.hide();
          }
        );
      }
    });
  }

  private getPreChangeStatusOppositeWording(status) {
    if(status == 'ACTIVE')
      return "deactivate";
    else if(status == 'INACTIVE')
      return "activate";

    return null;
  }

  private getChangeStatusOppositeWording(status) {
    if(status == 'ACTIVE')
      return "activated";
    else if(status == 'INACTIVE')
      return "deactivated";

    return null;
  }

  addNewUser() {
    if(this.newUserFormValid()) {

      // Copy values from form controls
      this.newUser.userName = this.newUser.formctrl_userName.value;
      this.newUser.userAccount = this.newUser.formctrl_userEmail.value;

      this.userService.addNewUser(this.newUser).subscribe(
        () => {
          this.userService.getAllUsers(); // We already changed the view by manipulating the array, so it's okay to make this async
          this.addUserModal.close();
          swal("Success!", "New user was added to the system.", "success");
          this.loader.hide();
        },
        err => {
          swal("Error!", "An error occured while adding the new user.", "error");
          console.log(err); 
          this.loader.hide();
        }
      );
    }
    else {
      swal("Error!", "User details are invalid.", "error");
    }
  }

  private updateCredential(userAccount, credential) {
    const userIndex = this.userService.users.findIndex(u => u.userAccount === userAccount)
    switch(credential) {
      case "memberOfEveryone":
        this.userService.users[userIndex].memberOfSystemAdministration = 'N';
        this.userService.users[userIndex].memberOfTicketAdministration = 'N';
        this.userService.users[userIndex].memberOfSectionHead = 'N';
        this.userService.users[userIndex].memberOfPartner = 'N';
        this.userService.users[userIndex].memberOfAssociate = 'N';
        this.userService.users[userIndex].memberOfEveryone = 'Y';
        break;
      case "memberOfAssociate":
        this.userService.users[userIndex].memberOfSystemAdministration = 'N';
        this.userService.users[userIndex].memberOfTicketAdministration = 'N';
        this.userService.users[userIndex].memberOfSectionHead = 'N';
        this.userService.users[userIndex].memberOfPartner = 'N';
        this.userService.users[userIndex].memberOfAssociate = 'Y';
        this.userService.users[userIndex].memberOfEveryone = 'Y';
        break;
      case "memberOfPartner":
        this.userService.users[userIndex].memberOfSystemAdministration = 'N';
        this.userService.users[userIndex].memberOfTicketAdministration = 'N';
        this.userService.users[userIndex].memberOfSectionHead = 'N';
        this.userService.users[userIndex].memberOfPartner = 'Y';
        this.userService.users[userIndex].memberOfAssociate = 'Y';
        this.userService.users[userIndex].memberOfEveryone = 'Y';
        break;
      case "memberOfSectionHead":
        this.userService.users[userIndex].memberOfSystemAdministration = 'N';
        this.userService.users[userIndex].memberOfTicketAdministration = 'N';
        this.userService.users[userIndex].memberOfSectionHead = 'Y';
        this.userService.users[userIndex].memberOfPartner = 'Y';
        this.userService.users[userIndex].memberOfAssociate = 'Y';
        this.userService.users[userIndex].memberOfEveryone = 'Y';
        break;
      case "memberOfTicketAdministration":
        this.userService.users[userIndex].memberOfSystemAdministration = 'N';
        this.userService.users[userIndex].memberOfTicketAdministration = 'Y';
        this.userService.users[userIndex].memberOfSectionHead = 'Y';
        this.userService.users[userIndex].memberOfPartner = 'Y';
        this.userService.users[userIndex].memberOfAssociate = 'Y';
        this.userService.users[userIndex].memberOfEveryone = 'Y';
        break;
      case "memberOfSystemAdministration":
        this.userService.users[userIndex].memberOfSystemAdministration = 'Y';
        this.userService.users[userIndex].memberOfTicketAdministration = 'Y';
        this.userService.users[userIndex].memberOfSectionHead = 'Y';
        this.userService.users[userIndex].memberOfPartner = 'Y';
        this.userService.users[userIndex].memberOfAssociate = 'Y';
        this.userService.users[userIndex].memberOfEveryone = 'Y';
        break;
    }
  }

  private newUserFormValid(): Boolean {
    if(!this.newUser.formctrl_userName.valid) {
      return false;
    }
    if(!this.newUser.formctrl_userEmail.valid) {
      return false;
    }

    return true;
  }
}