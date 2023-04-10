import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminMaintainTicketTemplatesComponent } from './admin-maintain-ticket-templates.component';

describe('AdminMaintainTicketTemplatesComponent', () => {
  let component: AdminMaintainTicketTemplatesComponent;
  let fixture: ComponentFixture<AdminMaintainTicketTemplatesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AdminMaintainTicketTemplatesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AdminMaintainTicketTemplatesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
