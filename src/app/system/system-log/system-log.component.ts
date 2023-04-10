import { Component, OnInit } from '@angular/core';
import { LogService } from '../../services/log.service';
import { LoaderService } from '../../services/loader/loader.service';

@Component({
  selector: 'app-system-log',
  templateUrl: './system-log.component.html',
  styleUrls: ['./system-log.component.css']
})
export class SystemLogComponent implements OnInit {

  filterByActionDescription = "";
  pageNbr: number = 1;

  constructor(
    public logService: LogService,
    private loader: LoaderService
  ) { }

  ngOnInit() {
    this.logService.getSystemLogEntries();
  }

  resetSystemLog() {
    swal({
      title: "All log entries will be deleted.",
      text: "Proceed?",
      icon: "warning",
      buttons: [true, true],
      dangerMode: true
    })
    .then((willCancel) => {
      if (willCancel) {
        this.logService.resetSystemLog().subscribe(
          () => {
            this.logService.getSystemLogEntries();
            swal("Success!", "System log was successfully reseted.", "success");
            this.loader.hide();
          },
          err => {
            swal("Error!", "An error occured while reseting the system log.", "error");
            console.error(err);
            this.loader.hide();
          }
        );
      }
    });
  }
}