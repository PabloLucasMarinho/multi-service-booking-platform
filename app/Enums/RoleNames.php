<?php

namespace App\Enums;

enum RoleNames: string
{
  case Owner = 'owner';
  case Admin = 'admin';
  case Employee = 'employee';
}
