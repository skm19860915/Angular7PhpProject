import { Component, Input, OnInit, ElementRef, ViewChild } from '@angular/core';
import { NgbDropdownConfig } from '@ng-bootstrap/ng-bootstrap';
import { UserService } from '../../services/user.service';

@Component({
  selector: 'app-user-sel-drop-down',
  templateUrl: './user-sel-drop-down.component.html',
  styleUrls: ['./user-sel-drop-down.component.css']
})
export class UserSelDropDownComponent implements OnInit {

  @Input() dropDownPlacement: string = 'bottom';
  @Input() showLabel: boolean = true;

  @ViewChild('searchInput') searchInput: ElementRef;

  filterOptionsVal: string = '';
  initDone = false;

  constructor(private ngbDropdownConfig: NgbDropdownConfig, public userService: UserService) { }

  ngOnInit() {
    this.ngbDropdownConfig.placement = this.dropDownPlacement;
    this.initDone = true;
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
    if (this.userService.selUser) {
      document.getElementById("user_selection_" + this.userService.selUser.userAccount).scrollIntoView(
        { block: 'center', behavior: 'auto' }
      );  
    }
  }

  /**
   * Resets the user dropDown
   */
  private clearSelection() {
    this.userService.selUser = this.userService.curUser;
  }
}