import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SystemMaintainSystemUsersComponent } from './system-maintain-system-users.component';

describe('SystemMaintainSystemUsersComponent', () => {
  let component: SystemMaintainSystemUsersComponent;
  let fixture: ComponentFixture<SystemMaintainSystemUsersComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SystemMaintainSystemUsersComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SystemMaintainSystemUsersComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
