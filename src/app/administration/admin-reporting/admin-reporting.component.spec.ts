import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminReportingComponent } from './admin-reporting.component';

describe('AdminReportingComponent', () => {
  let component: AdminReportingComponent;
  let fixture: ComponentFixture<AdminReportingComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AdminReportingComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AdminReportingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
