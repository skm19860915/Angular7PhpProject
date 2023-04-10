import { Component } from '@angular/core';
import { UserService } from './services/user.service';
import { Router } from '@angular/router';
const { version: appVersion } = require('../../package.json');

declare var $: any;

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {

  appName = 'Event Ticketing System';
  copyrightYear = new Date().getFullYear();
  showEntryModal = true;
  appVersion;

  constructor(public userService: UserService, public router: Router) {
    this.appVersion = appVersion;
  }

  ngOnInit() {
    // Close burger menu after onClick
    $(document).on('click', '.second-top-navbar', function (e) {
      if ($(e.target).is('a') || $(e.target).hasClass('dropdown-item')) {
        $('.navbar-collapse').collapse('hide');
      }
    });
  }

  viewPermitted(view): Boolean {
    switch (view) {
      case "administration":
        if (this.userService.curUser.memberOfTicketAdministration != false || this.userService.curUser.memberOfSystemAdministration != false)
          return true;
        break;
      case "system":
        if (this.userService.curUser.memberOfSystemAdministration != false)
          return true;
        break;
    }

    return false;
  }
}
