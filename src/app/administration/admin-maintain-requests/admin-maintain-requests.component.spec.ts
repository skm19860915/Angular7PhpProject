import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminMaintainRequestsComponent } from './admin-maintain-requests.component';

describe('AdminMaintainRequestsComponent', () => {
  let component: AdminMaintainRequestsComponent;
  let fixture: ComponentFixture<AdminMaintainRequestsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AdminMaintainRequestsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AdminMaintainRequestsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
