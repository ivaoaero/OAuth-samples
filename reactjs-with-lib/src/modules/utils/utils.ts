import { UserDto } from "../auth";

export const publicName = (user: UserDto) => `${user.firstName} ${user.lastName} (${user.id})`
