import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminMaintainEventTemplatesComponent } from './admin-maintain-event-templates.component';

describe('AdminMaintainEventTemplatesComponent', () => {
  let component: AdminMaintainEventTemplatesComponent;
  let fixture: ComponentFixture<AdminMaintainEventTemplatesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AdminMaintainEventTemplatesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AdminMaintainEventTemplatesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
