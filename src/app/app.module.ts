import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { RouterModule, Routes } from '@angular/router';
import { HttpClientModule } from '@angular/common/http';
import { NgModule } from '@angular/core';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { DatePipe } from '@angular/common';
import { CalendarModule } from 'primeng/calendar';
import { NgxPaginationModule } from 'ngx-pagination';

import { AppComponent } from './app.component';
import { OverviewComponent } from './overview/overview.component';
import { RequestsComponent } from './requests/requests.component';

import { EventService } from './services/event.service';
import { TicketService } from './services/ticket.service';
import { RequestService } from './services/request.service';
import { AssignmentService } from './services/assignment.service';
import { UserService} from './services/user.service';
import { AssignmentsComponent } from './assignments/assignments.component';
import { AdministrationComponent } from './administration/administration.component';
import { APP_BASE_HREF } from '@angular/common';
import { environment } from '../environments/environment';
import { AdministrationGuard } from './guards/administration.guard';
import { AdminOverviewComponent } from './administration/admin-overview/admin-overview.component';
import { AdminMaintainRequestsComponent } from './administration/admin-maintain-requests/admin-maintain-requests.component';
import { AdminMaintainAssignmentsComponent } from './administration/admin-maintain-assignments/admin-maintain-assignments.component';
import { AdminMaintainEventsComponent } from './administration/admin-maintain-events/admin-maintain-events.component';
import { AdminMaintainTicketsComponent } from './administration/admin-maintain-tickets/admin-maintain-tickets.component';
import { VenueService } from './services/venue.service';

import { Uppercase } from './directives/uppercase';
import { NoDblClickDirective } from './directives/no-dbl-click';
import { ClickOutsideDirective } from './directives/click-outside';
import { LoaderComponent } from './loader/loader.component';
import { LoaderService } from './services/loader/loader.service';
import { LogService } from './services/log.service';
import { SystemComponent } from './system/system.component';
import { SystemGuard } from './guards/system.guard';
import { SystemOverviewComponent } from './system/system-overview/system-overview.component';
import { SystemLogComponent } from './system/system-log/system-log.component';
import { SystemMaintainSystemUsersComponent } from './system/system-maintain-system-users/system-maintain-system-users.component';
import { ObjectFilterPipe } from './pipes/object-filter.pipe';
import { ObjectTextFilterPipe } from './pipes/object-text-filter.pipe';
import { OptionFilterPipe } from './pipes/option-filter.pipe';
import { SpecialRequestsComponent } from './special-requests/special-requests.component';
import { FilterDropDownComponent } from './util/filter-drop-down/filter-drop-down.component';
import { UserSelDropDownComponent } from './util/user-sel-drop-down/user-sel-drop-down.component';
import { ActionDropDownComponent } from './util/action-drop-down/action-drop-down.component';
import { AdminMaintainSpecialRequestsComponent } from './administration/admin-maintain-special-requests/admin-maintain-special-requests.component';
import { TransferUserDropDownComponent } from './util/transfer-user-drop-down/transfer-user-drop-down.component';
import { AdminMaintainTicketTemplatesComponent } from './administration/admin-maintain-ticket-templates/admin-maintain-ticket-templates.component';
import { InlineEditComponent } from './util/inline-edit/inline-edit.component';
import { FocusDirective } from './directives/focus';
import { AdminMaintainEventTemplatesComponent } from './administration/admin-maintain-event-templates/admin-maintain-event-templates.component';
import { AdminReportingComponent } from './administration/admin-reporting/admin-reporting.component';
import {AutocompleteLibModule} from 'angular-ng-autocomplete';

const appRoutes: Routes = [
  { path: '', redirectTo: '/overview', pathMatch: 'full' },
  { path: 'overview', component: OverviewComponent },
  { path: 'requests', component: RequestsComponent },
  { path: 'special-requests', component: SpecialRequestsComponent },
  { path: 'assignments', component: AssignmentsComponent },
  { path: 'system', component: SystemComponent, canActivate: [SystemGuard], children: [
    { path: '', redirectTo: 'overview', pathMatch: 'full' },
    { path: 'overview', component: SystemOverviewComponent, canActivate: [SystemGuard] },
    { path: 'log', component: SystemLogComponent, canActivate: [SystemGuard], data: { breadcrumb: "System Log"} },
    { path: 'users', component: SystemMaintainSystemUsersComponent, canActivate: [SystemGuard], data: { breadcrumb: "Maintain System Users"} }
  ] },
  { path: 'administration', component: AdministrationComponent, canActivate: [AdministrationGuard], children: [
    { path: '', redirectTo: 'overview', pathMatch: 'full' },
    { path: 'overview', component: AdminOverviewComponent, canActivate: [AdministrationGuard] },
    { path: 'requests', component: AdminMaintainRequestsComponent, canActivate: [AdministrationGuard], data: { breadcrumb: "Maintain Ticket Requests"} },
    { path: 'special-requests', component: AdminMaintainSpecialRequestsComponent, canActivate: [AdministrationGuard], data: { breadcrumb: "Maintain Special Ticket Requests"} },
    { path: 'assignments', component: AdminMaintainAssignmentsComponent, canActivate: [AdministrationGuard], data: { breadcrumb: "Maintain Ticket Assignments"} },
    { path: 'events', component: AdminMaintainEventsComponent, canActivate: [AdministrationGuard], data: { breadcrumb: "Maintain Events"} },
    { path: 'tickets', component: AdminMaintainTicketsComponent, canActivate: [AdministrationGuard], data: { breadcrumb: "Maintain Tickets"} },
    { path: 'ticket-templates', component: AdminMaintainTicketTemplatesComponent, canActivate: [AdministrationGuard], data: { breadcrumb: "Maintain Ticket Templates"} },
    { path: 'event-templates', component: AdminMaintainEventTemplatesComponent, canActivate: [AdministrationGuard], data: { breadcrumb: "Maintain Event Templates"} },
    { path: 'reporting', component: AdminReportingComponent, canActivate: [AdministrationGuard], data: { breadcrumb: "Reporting"} }
  ] }
];

@NgModule({
  declarations: [
    AppComponent,
    OverviewComponent,
    RequestsComponent,
    AssignmentsComponent,
    AdministrationComponent,
    AdminOverviewComponent,
    AdminMaintainRequestsComponent,
    AdminMaintainAssignmentsComponent,
    AdminMaintainEventsComponent,
    AdminMaintainTicketsComponent,
    Uppercase,
    NoDblClickDirective,
    ClickOutsideDirective,
    FocusDirective,
    LoaderComponent,
    SystemComponent,
    SystemOverviewComponent,
    SystemLogComponent,
    SystemMaintainSystemUsersComponent,
    OptionFilterPipe,
    ObjectFilterPipe,
    ObjectTextFilterPipe,
    SpecialRequestsComponent,
    FilterDropDownComponent,
    UserSelDropDownComponent,
    ActionDropDownComponent,
    AdminMaintainSpecialRequestsComponent,
    TransferUserDropDownComponent,
    AdminMaintainTicketTemplatesComponent,
    InlineEditComponent,
    AdminMaintainEventTemplatesComponent,
    AdminReportingComponent
  ],
  imports: [
    BrowserModule,
    RouterModule.forRoot(
      appRoutes,
      { enableTracing: false } // True only for debugging
    ),
    NgbModule.forRoot(),
    HttpClientModule,
    FormsModule,
    ReactiveFormsModule,
    BrowserAnimationsModule,
    CalendarModule,
    NgxPaginationModule,
    AutocompleteLibModule
  ],
  providers: [
    { provide: APP_BASE_HREF, useValue: environment.baseHref },
    //{ provide: HTTP_INTERCEPTORS, useClass: CacheInterceptor, multi: true },
    EventService,
    VenueService,
    TicketService,
    RequestService,
    UserService,
    AssignmentService,
    AdministrationGuard,
    SystemGuard,
    DatePipe,
    LoaderService,
    LogService
  ],
  bootstrap: [AppComponent]
})
export class AppModule {
  constructor(private userService: UserService) {
    this.userService.getCurUserFromAD();
    this.userService.getAllUsers();
  }
}
