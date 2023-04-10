import { Injectable } from '@angular/core';
import { environment } from '../../environments/environment';
import { HttpClient } from '@angular/common/http';
import { LoaderService } from './loader/loader.service';
import { LogEntry } from './observables/logentry';

@Injectable()
export class LogService {

  private httpUrlGetAllLogEntries = environment.backendPath + 'getSystemLog.php';
  private httpUrlResetSystemLog = environment.backendPath + 'clearSystemLog.php';

  public entries = [];
  public actionTypes = [];

  constructor(private http: HttpClient, private loader: LoaderService) { }

  getSystemLogEntries(): void {
    this.loader.show();
    this.http.get<LogEntry>(this.httpUrlGetAllLogEntries + "?tsp=" + new Date()).subscribe(
      data => this.fetchLogEntryData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    );
  }

  private fetchLogEntryData(data): void {
    this.entries = [];
    data.forEach(entry => {
      if(this.actionTypes.indexOf(entry["Action"]) == -1)
        this.actionTypes.push(entry["Action"]);
      this.entries.push(entry);
    });
    this.loader.hide();
  }

  resetSystemLog() {
    this.loader.show();
    return this.http.get(this.httpUrlResetSystemLog + "?tsp=" + new Date());
  }
}
