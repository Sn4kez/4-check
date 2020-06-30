export class InspectionPlan {
	constructor(obj = {}) {
		this.id = obj.id || null;
		this.name = obj.name;
		this.monday = obj.monday || 0;
		this.tuesday = obj.tuesday || 0;
		this.wednesday = obj.wednesday || 0;
		this.thursday = obj.thursday || 0;
		this.friday = obj.friday || 0;
		this.saturday = obj.saturday || 0;
		this.sunday = obj.sunday || 0;
		this.factor = obj.factor || 0;
		this.startDate = obj.startDate;
		this.endDate = obj.endDate;
		this.startTime = obj.startTime;
		this.endTime = obj.endTime;
		this.checklist = obj.checklist;
		this.user = obj.user;
		this.company = obj.company;
		this.type = obj.type;
	}
}
