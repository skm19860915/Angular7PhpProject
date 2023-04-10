import { Component, EventEmitter, Input, Output, ElementRef, ViewChild } from '@angular/core';
import { UserService } from '../../services/user.service';

@Component({
  selector: 'app-transfer-user-drop-down',
  templateUrl: './transfer-user-drop-down.component.html',
  styleUrls: ['./transfer-user-drop-down.component.css']
})
export class TransferUserDropDownComponent {

  filterOptionsVal: string = '';
  transferUser: any;

  @ViewChild('searchInput') searchInput: ElementRef;

  @Input() excludedUser: any;
  @Input() menuPlacement: string;
  @Input() smallBtn: boolean = false;
  @Input() dropdownCenteredOnScreen: boolean = false;
  @Output() selectOptionCallback = new EventEmitter<boolean>();

  constructor(public userService: UserService) { }
  
  private selectOption(user) {
    this.transferUser = user;
    this.selectOptionCallback.emit(this.transferUser);
  }

  /**
   * Gets called if user dropDown gets opened or closed
   * 
   * @param opened state of dropDown
   */
  dropDownOpenChange(opened) {
    if (opened) {
      this.afterDropDownOpenedActions();
    }
    else {
      this.filterOptionsVal = '';
    }
  }

  /**
   * Needed to set a short timeout until scrollIntoView() gets fired
   * 
   * @param ms the wait time in milliseconds
   */
  timeout(ms) {
    return new Promise(resolve => setTimeout(resolve, ms))
  }

  /**
   * Searches for the element by the user name as id 
   * and scrolls the dropDown list to the user element
   */
  async afterDropDownOpenedActions() {
    await this.timeout(10);
    this.searchInput.nativeElement.focus();
    if (this.transferUser) {
      document.getElementById("transfer_user_" + this.transferUser.userAccount).scrollIntoView(
        { block: 'center', behavior: 'auto' }
      );
    }
  }
}
