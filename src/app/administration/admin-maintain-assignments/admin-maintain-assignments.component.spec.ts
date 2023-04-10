import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminMaintainAssignmentsComponent } from './admin-maintain-assignments.component';

describe('AdminMaintainAssignmentsComponent', () => {
  let component: AdminMaintainAssignmentsComponent;
  let fixture: ComponentFixture<AdminMaintainAssignmentsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AdminMaintainAssignmentsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AdminMaintainAssignmentsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
