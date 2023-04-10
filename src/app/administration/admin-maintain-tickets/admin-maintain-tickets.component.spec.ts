import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminMaintainTicketsComponent } from './admin-maintain-tickets.component';

describe('AdminMaintainTicketsComponent', () => {
  let component: AdminMaintainTicketsComponent;
  let fixture: ComponentFixture<AdminMaintainTicketsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AdminMaintainTicketsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AdminMaintainTicketsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
