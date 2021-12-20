var group = [
  "A",
  "B",
  "C"
]

var planner = {
  "A":[
    "PlannA1",
    "PlannA2",
    "PlannA3",
  ],
  "B":[
    "PlannB1",
  ],
  "C":[
    "PlannC1",
  ]
}

var tasks = {
  "PlannA1":[
    "Create Polling A",
    "Create Polling 2 A"
  ],
  "PlannA2":[],
  "PlannA3":[
    "Editor nature A",
    "Create Banner A",
    "Create Block Chain A"
  ],
  "PlannB1":[],
  "PlannC1":[],
}

var dataGroups = [],
    dataPlannerGroups = [],
    dataGroupPlannerTasks = [];

function fetch(query){
  return new Promise((resolve) => {
    query(resolve)
  })
}

function searchGroup(){
  return fetch(function(resolve){
    var groupS = group
    setTimeout(() => {
      for(var i = 0; i<groupS.length; i++){
        dataGroups.push(groupS[i])
      }
      resolve("Getting Group")
    }, 1000)
  });
}


function searchPlanner(querySyncronus){
  return fetch(async function(resolve){
    var data = dataGroups,
        dataLength = data.length;
      for await (let getPlanner of querySyncronus(data)){}
      resolve("Getting Planner");
  });
}

function searchTask(querySyncronus){
  return fetch(async function(resolve){
    var data = dataPlannerGroups,
        dataLength = dataPlannerGroups.length;
      for await (let getTask of querySyncronus(data)){}
      resolve("Getting Task")
  })
}

function* processingSearchTaskAll(){
  yield "Start";
  
  yield searchGroup();
  
  yield searchPlanner(function* (group){
    var data = group,
      dataLength = group.length;
    for(let dGroup of data){ // read per group
      var nameGroup = dGroup
      yield fetch(function(resolve){
        setTimeout(() => {
          let dataPlanner = planner[nameGroup]
          for(let plann of dataPlanner){
            dataPlannerGroups.push({
              group:nameGroup,
              planner:plann 
            })
          }
          resolve(dataPlannerGroups);
        }, 1000)
      })
    }
  });
  
  yield searchTask(function* (planner){
    var data = planner,
        dataLength = data.length;
    for(let dPlanner of data){
      var nameGroup = dPlanner.group,
          namePlanner = dPlanner.planner;
      yield fetch(function(resolve){
        setTimeout(() => {
          let dataTask = tasks[namePlanner]
          for(let task of dataTask){
            dataGroupPlannerTasks.push({
              group:nameGroup,
              planner:namePlanner,
              task:task,
            })
          }
          resolve(dataGroupPlannerTasks)
        }, 1450)
      })
    }
  });
  
  yield dataGroupPlannerTasks;
	  
  yield "Finish";
}

(async function(){
  for await (let process of processingSearchTaskAll()){
    console.log(process)
  }
})()
























