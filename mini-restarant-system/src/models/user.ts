export interface User{
    id:number,
    name:string,
    email:string,
    avatar?:string,
    gender?:string,
    phone?:string | null,
    role?: string
}
