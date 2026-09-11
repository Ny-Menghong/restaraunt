export interface Food{
    id:number;
    category_id:number;
    name:string;
    description:string;
    price:number;
    image:string;
    quantity:number;
    status:boolean;
    created_at:Date;
    updated_at:Date;
    category?: { id: number; name: string };
}