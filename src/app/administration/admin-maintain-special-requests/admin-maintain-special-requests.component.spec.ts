import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminMaintainSpecialRequestsComponent } from './admin-maintain-special-requests.component';

describe('AdminMaintainSpecialRequestsComponent', () => {
  let component: AdminMaintainSpecialRequestsComponent;
  let fixture: ComponentFixture<AdminMaintainSpecialRequestsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AdminMaintainSpecialRequestsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AdminMaintainSpecialRequestsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
