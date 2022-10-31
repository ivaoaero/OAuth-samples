// Partial DTO from https://api.ivao.aero/docs 

interface UserRatingDto {
    isPilot: boolean;
    isAtc: boolean;
}

interface UserHoursDto {
    type: "atc" | "pilot" | "staff";
    hours: number;
}

interface UserStaffPositionDto {
    id: string;
    staffPositionId: string;
    divisionId: string;
}

export interface UserDto {
    id: number;
    firstName: string;
    lastName: string;
    publicNickname: string;
    divisionId: string;
    rating: UserRatingDto;
    hours: UserHoursDto[];
    userStaffPositions: UserStaffPositionDto[];
}