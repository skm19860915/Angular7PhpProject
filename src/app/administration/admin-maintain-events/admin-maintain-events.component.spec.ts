import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminMaintainEventsComponent } from './admin-maintain-events.component';

describe('AdminMaintainEventsComponent', () => {
  let component: AdminMaintainEventsComponent;
  let fixture: ComponentFixture<AdminMaintainEventsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AdminMaintainEventsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AdminMaintainEventsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
