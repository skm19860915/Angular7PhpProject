import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SpecialRequestsComponent } from './special-requests.component';

describe('SpecialRequestsComponent', () => {
  let component: SpecialRequestsComponent;
  let fixture: ComponentFixture<SpecialRequestsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SpecialRequestsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SpecialRequestsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
