import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { User } from './observables/user';
import { environment } from '../../environments/environment';
import { LoaderService } from './loader/loader.service';

@Injectable()
export class UserService {

  public statusList = [
    'ACTIVE',
    'INACTIVE'
  ];

  private getAllUsersURL = environment.backendPath + 'getAllSystemUsers.php';
  private getCurrentUserURL = environment.backendPath + 'getCurrentUserAndMemberships.php';
  private addNewUserURL = environment.backendPath + 'addSystemUser.php';
  private updateUserURL = environment.backendPath + 'updateSystemUser.php';
  
  curUser = new User;
  selUser = new User;
  users = [];
  activeUsers = [];
  userGroups = [
    'Everyone',
    'Associate',
    'Partner',
    'Section Head',
    'Administration'
  ];

  constructor(private http: HttpClient, private loader: LoaderService) { }

  ngOnInit() {
    this.getCurUserFromAD();
    this.getAllUsers();
  }

  getAllUsers(): void {
    this.loader.show();
    this.http.get<User>(this.getAllUsersURL + "?tsp=" + new Date()).subscribe(
      data => this.fetchUserData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    );
  }

  getCurUserFromAD(): void {
    this.loader.show();
    this.http.get<User>(this.getCurrentUserURL + "?tsp=" + new Date()).subscribe(
      data => this.fetchCurUserData(data),
      err => {
        console.error(err);
        this.loader.hide();
      }
    );
  }

  updateUser(userAccount) {
    this.loader.show();
    const updateUser = this.users.filter(u => u.userAccount == userAccount);
    return this.http.post(this.updateUserURL, JSON.stringify(updateUser[0]));
  }

  addNewUser(userObj: User) {
    this.loader.show();
    return this.http.post<any>(this.addNewUserURL, JSON.stringify(userObj));
  }

  private fetchCurUserData(data): void {
    this.curUser = data;
    this.selUser = this.curUser;
    this.loader.hide();
  }

  private fetchUserData(data): void {
    this.users = data;
    this.activeUsers = this.users.filter(u => u.userStatus === 'ACTIVE');
    this.loader.hide();
  }
}